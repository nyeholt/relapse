<?php

class Note extends MappedObject
{
    public $userid;
    public $title;
    public $note;
    
    /**
     * What's the type of the item it's attached to?
     *
     * @var string
     */
    public $attachedtotype;
    
    /**
     * What's the id of the item?
     *
     * @var int
     */
    public $attachedtoid;
    
    public $constraints = array();
    public $requiredFields = array('title');
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s', time());
        $this->constraints['title'] = new Zend_Validate_StringLength(3);
    }
}
?>