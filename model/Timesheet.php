<?php
/**
 * A timesheet can be for either a Project or a
 * Client. It marks a bunch of timesheet records
 * as belonging to it, and shows the amount of time 
 * for those records.  
 */
class Timesheet extends Bindable
{
    public $id;
    public $created;
    public $updated;
    
    public $title;
    public $projectid;
    public $clientid;
    
    public $locked;

    public $from;
    public $to;

    /**
     * @var array
     */
    public $tasktype;
}
?>