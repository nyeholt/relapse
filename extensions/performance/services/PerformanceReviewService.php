<?php
class PerformanceReviewService
{
    /**
     * @var DbService
     */
    public $dbService;
    
    public function saveReview(PerformanceReview $review, $params)
    {
        $toArchive = null;
        
        // If we're EDITING a review, we have to save off the current one as an archive
        // so we clone it before we bind any new values. That way we can save it
        // without modification. 
        if ($review->id) {
            $toArchive = clone $review;
            if (!$toArchive->originalversion) {
                $toArchive->originalversion = $toArchive->id;
            }
        }
        
        // Bind new values
        $review->bind($params);
        
        $validator = new ModelValidator();
        if (!$validator->isValid($review)) {
            throw new InvalidModelException($validator->getErrors());
        }
        
        
        if ($review->id) {
            // Even though the clone above should copy this version info, 
            // there are cases (ie after first creation) that the original
            // version isn't set, so just make sure here
            $review->originalversion = $toArchive->originalversion;
            
            // Set the ID = nothing so that when it gets saved, it's CREATED
            // as a new review instead
            $review->id = null;
        } /*else {
            if (!$faq->author) {
                $faq->author = za()->getUser()->getUsername();                
            }

            $faq->authored = date('Y-m-d H:i:s');
        }*/
        
        $review->modifiedby = za()->getUser()->getUsername();

        $newReview = $this->dbService->saveObject($review);
        
        if ($toArchive) {
            $toArchive->nextversionid = $newReview->id;
            $this->dbService->saveObject($toArchive);
        }

        return $newReview;
    }
}
?>