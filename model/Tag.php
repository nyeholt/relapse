<?php

class Tag extends Bindable
{
    public $id;
    public $tag;
    public $itemid;
    public $itemtype;
    public $uid;
    
    public $requiredFields = array('tag', 'itemid', 'itemtype');
}
?>