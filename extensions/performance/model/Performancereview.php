<?php
class PerformanceReview extends Bindable 
{
    public $id;
    public $created;
    public $updated;
    public $modifiedby;
    public $title;

    /**
     * The ID of the review that is the next most recent than this one
     * @var int
     */
    public $nextversionid = 0;
    
    /**
     * What's the original version of this version thread?
     *
     * @var int
     */
    public $originalversion = 0;
    
    public $username;

    public $from;
    public $to;

    public $position;
    
    public $reportsto;

    /**
     * @var object
     */
    public $shortgoals;
    
    /**
     * @var object
     */
    public $mediumgoals;
    
    /**
     * @var object
     */
    public $longgoals;

    /**
     * @var object
     */
    public $development;

    /**
     * @var object
     */
    public $intermediatereviews;

    public $signedemployee;

    public $signedmanager;

    public $managercomments;

    public $employeecomments;
    
    public $searchableFields = array();
    
    public $requiredFields = array('title');
}
?>
