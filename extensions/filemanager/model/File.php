<?php

class File extends MappedObject
{
    public $filename;
    public $title;
    public $description;
    public $isprivate; 
    
    public $path;
    public $owner;
    
    public $requiredFields = array('filename');
    
    private $imageExt = array('jpg', 'gif', 'png', 'bmp', 'jpeg');
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
    }
    
    /**
     * Get the actual title of this file
     *
     * @return unknown
     */
    public function getTitle()
    {
        return empty($this->title) ? $this->filename : $this->title;
    }
    
    public function getExtension()
    {
        $ext = substr(strrchr($this->filename, '.'), 1);
        return $ext;
    }
    
    public function isImage()
    {
        $ext = mb_strtolower($this->getExtension());
        return in_array($ext, $this->imageExt); 
    }

}
?>