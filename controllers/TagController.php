<?php

class TagController extends NovemberController
{
    /**
     * @var TagService
     */
    public $tagService;
    
    /**
     * The tag controller will generate a tag cloud
     */
    public function indexAction()
    {
        $type = $this->_getParam('type');
        // if there's a type set, we want to limit the query a bit
        $where = array();
        if ($type) {
            $where = array('itemtype='=> $type);
        }
        $this->view->type = $type;
        $this->view->tags = $this->tagService->getTags($where, 'tag asc');
        
        $min = 1;
        $max = 1;
        foreach ($this->view->tags as $tag) {
            if ($tag['frequency'] < $min) {
                $min = $tag['frequency'];
            }
            if ($tag['frequency'] > $max) {
                $max = $tag['frequency'];
            }
        }
        
        $this->view->diff = $max - $min;
        $this->view->max = $max;
        $this->view->min = $min;
        
        $this->view->scale = ($this->view->diff > 0) ? $this->view->diff / 6 : 1;
        
        $this->renderView('tag/cloud.php');
    }
    
    
    public function suggestAction()
    {
        $stub = trim($this->_getParam('q'));
        if (mb_strlen($stub) > 1) {
            $suggestions = $this->tagService->getTags(array('tag like ' => $stub.'%'));
            foreach ($suggestions as $tag) {
                echo $tag['tag']."\r\n";
            }
        }
    }
}
?>