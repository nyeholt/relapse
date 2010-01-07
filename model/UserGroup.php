<?php

class UserGroup extends Bindable 
{
    public $id;
    public $title;
    public $description;
    
    /**
     * The parent of this group
     *
     * @var string
     */
    public $parentpath;
    
    public $updated;
    public $created;
    
    public $constraints = array();
    public $requiredFields = array('title');

    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s', time());
        $this->constraints['title'] = new Zend_Validate_StringLength(4);
    }
    
    public function getPath()
    {
        $path = rtrim($this->parentpath, '-');
	    $path .= '-'.$this->id.'-';
	    return trim($path);
    }
}
?>