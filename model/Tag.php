<?php

class Tag extends MappedObject
{
    public $tag;
    public $itemid;
    public $itemtype;
    public $uid;
    
    public $requiredFields = array('tag', 'itemid', 'itemtype');
}
?>