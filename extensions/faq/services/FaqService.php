<?php

include_once 'extensions/faq/model/Faq.php';

class FaqService
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * @var TagService
     */
    public $tagService;
    
    /**
     * Saves an FAQ
     * 
     * Saving an faq involves creating a new one each time
     * If the faq is actually a modification, the old one
     * is updated to point to its 'next' version 
     *
     * @param Faq $faq
     * @param array $params
     */
    public function saveFaq($faq, $params)
    {
        // check to see if there's an existing ID at all
        $urlName = ifset($params, 'faqurl');
        if (!$urlName || !mb_strlen($urlName)) {
            // create a nice friendly url name
            $urlName = $this->createFriendlyName(ifset($params, 'title'));
            $params['faqurl'] = $urlName;
        }

        $toArchive = null;
        
        // If we're EDITING a faq, we have to save off the current one as an archive
        // so we clone it before we bind any new values. That way we can save it
        // without modification. 
        if ($faq->id) {
            $toArchive = clone $faq;
            if (!$toArchive->originalversion) {
                $toArchive->originalversion = $toArchive->id;
            }
        } 
        
        // Bind new values
        $faq->bind($params);
        
        $validator = new ModelValidator();
        if (!$validator->isValid($faq)) {
            throw new InvalidModelException($validator->getErrors());
        }
        
        // Check to see if the faqurl needs to be validated. We validate when there's
        // a new FAQ, or if the faqurl has changed
        if (!$faq->id || $faq->faqurl != $toArchive->faqurl) {
            if (!$this->validateFaqUrl($faq)) {
                throw new InvalidModelException(array('FAQ title must produce a unique URL, '.$faq->faqurl.' is already taken'));
            }
        }
        
        if ($faq->id) {
            // Even though the clone above should copy this version info, 
            // there are cases (ie after first creation) that the original
            // version isn't set. 
            $faq->originalversion = $toArchive->originalversion;
            $faq->id = null;
        } else {
            if (!$faq->author) {
                $faq->author = za()->getUser()->getUsername();                
            }

            $faq->authored = date('Y-m-d H:i:s');
        }
        
        $faq->modifiedby = za()->getUser()->getUsername();

        $newFaq = $this->dbService->saveObject($faq);
        
        if ($toArchive) {
            $toArchive->nextversionid = $newFaq->id;
            $this->dbService->saveObject($toArchive);
            // We're going to delete any tags this faq has also
            // to prevent pollution of the tag soup
            $this->tagService->deleteTags($toArchive);
        }

        return $newFaq;
    }
    
    /**
     * Checks to make sure the FAQ url isn't already being
     * used. 
     */
    private function validateFaqUrl($faq)
    {
        // search for it, but only look at those FAQs that aren't another version
        // of the faq being checked .
        $where = array('faqurl='=>$faq->faqurl);
        if ($faq->originalversion) {
            $where['originalversion<>'] = $faq->originalversion;
        }
        $existing = $this->dbService->getObjects('Faq', $where);

        return count($existing) == 0;
    }
    
    /**
     * Format a friendly name for an FAQ entry. 
     */
    private function createFriendlyName($title)
    {
        // Convert to a nice name
        $path = mb_strtolower($title);
        $path = preg_replace('/[ _+]/', '-', $path);
        $path = str_replace('&', 'and', $path);
        $path = preg_replace("[^a-z0-9:.-]", "", $path);
        return trim($path, '-');
    }

    /**
     * Gets all the versions for a given FAQ
     * @return ArrayList
     */
    public function getFaqVersions(Faq $faq)
    {
        return $this->dbService->getObjects('Faq', array('originalversion='=>$faq->originalversion, 'nextversionid<>'=>0), 'id desc');
    }
}
?>