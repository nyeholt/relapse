<?php

class Faq extends Bindable
{
    public $id;
    public $title;
    public $description;
    
    public $updated;
    public $created;
    
    /**
     * We need to separately store the authoredon date
     * because created refers to the date that this version was created
     *
     * @var unknown_type
     */
    public $authored;
    
    public $author;
    public $modifiedby;

    /**
     * The ID of the faq that is the next most recent than this one
     * @var int
     */
    public $nextversionid = 0;
    
    /**
     * What's the original version of this version thread
     *
     * @var int
     */
    public $originalversion = 0;
    
    /**
     * The friendly URL title of the faq, unique for all faqs
     *
     * @var string
     */
    public $faqurl;
    
    /**
     * @var text
     */
    public $faqcontent;
    
    public $requiredFields = array('title', 'faqurl', 'faqcontent');
}
?>