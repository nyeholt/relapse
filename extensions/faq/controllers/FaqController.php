<?php

class FaqController extends NovemberController 
{
    /**
     * @var FaqService
     */
    public $faqService;
    
    /**
     * @var TagService
     */
    public $tagService;
    
    /**
     * @var UserService
     */
    public $userService;
    
    /**
     * @var SearchService
     */
    public $searchService;
    
    /**
     * Only list faqs that aren't a version
     */
    public function listAction()
    {
        $tag = $this->_getParam('tag');
        $idClause = null;
        
        $where = array('nextversionid='=>0);
        if (mb_strlen($tag)) {
            // okay, we need to have an in () clause
            $ids = $this->tagService->getTaggedItems($tag, 'faq');
            if (count($ids)) {
                $where['id in '] = new Zend_Db_Expr('('.implode(",", $ids).')');
            }
        }
        
        $query = $this->_getParam('query');
        if (mb_strlen($query)) {
            // search for faqs
            $hits = $this->searchService->search($query);
            $ids = array();
            foreach ($hits as $hit) {
                if (mb_strtolower($hit->type) == 'faq' && !$hit->nextversionid) {
                    $ids[] = $hit->__get('id');
                    
                }
            }

            if (count($ids)) {
                $where['id in '] = new Zend_Db_Expr('('.implode(",", $ids).')');
            }
        }
        
        // Get all the tags for objects of type 'faq'
        $this->view->tags = $this->tagService->getTags(array('itemtype='=>'faq'), "frequency desc", 20); 
        $this->view->listSize = 10;
        $this->view->totalCount = $this->dbService->getObjectCount($where, 'Faq');
        $this->view->pagerName = 'page';
        $this->view->items = $this->dbService->getObjects('Faq', $where, 'authored desc', $this->_getParam('page', 1), $this->view->listSize);
        $this->renderView('faq/list.php');
    }
    
    /**
     * When we're about to edit $model, we need to make sure to
     * get any tags it might have so they can be edited 
     */
    protected function prepareForEdit($model)
    {
        $model->tags = "";
        // if there's an id, get all the objects for it
        if ($model->id) {

            $tags = $this->tagService->getItemTags($model);
            $tagStr = "";
            $sep = "";
            foreach ($tags as $tag) {
                $tagStr .= $sep.$tag->tag;
                $sep = ',';
            }
            $model->tags = $tagStr;
        }
        if ($model->nextversionid) {
            throw new Exception("Cannot edit a version");
        }
        $this->view->authors = $this->userService->getUserList();
    }
    
    /**
     * Get the tags this item has been tagged with. 
     */
    protected function prepareForView($model)
    {
        $model->tags = "";
        // if there's an id, get all the objects for it
        if ($model->id) {

            $tags = $this->tagService->getItemTags($model);
            $tagStr = "";
            $sep = "";
            foreach ($tags as $tag) {
                $tagStr .= $sep.$tag->tag;
                $sep = ',';
            }

            $model->tags = $tagStr;
            
            // Get all the versions of this FAQ
            $this->view->versions = $this->faqService->getFaqVersions($model);
        }
    }
    
    /**
     * Overridden save so that we can do the 'new item instead of updating old'
     * 
     */
    public function saveAction()
    {
        $model = $this->byId();
        if ($model == null) {
            $model = new Faq();
        }

        try {
            $params = $this->filterParams($this->_getAllParams());
            
            $this->dbService->beginTransaction();
            $model = $this->faqService->saveFaq($model, $params);

            // Now save the tags for that model
            $tagstr = $this->_getParam('tags');
            if (!mb_strlen($tagstr)) {
                // go and get some suggested tags!
                if (mb_strlen($model->faqcontent)) {
                    $possibles = $this->tagService->suggestTagsFor($model->faqcontent);
                    if (count($possibles) > 0) {
                        $tagstr = implode(",", $possibles);
                    }
                }
            }
            
            if ($tagstr) {
                $this->log->debug("Adding tags $tagstr to faq #$model->id");
                $this->tagService->saveTags($model, $tagstr);
            }
            $this->dbService->commit();
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        }
        
        $this->redirect('faq', 'view', array('id'=>$model->id));
    }
}
?>