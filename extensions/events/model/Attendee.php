<?php

class Attendee extends Bindable 
{
    public $id;
    public $created;
    public $updated;
    
    public $eventid;
    public $eventuserid;
    
    /**
     * Tracks who refered someone to an event
     *
     * @var int
     */
    public $refererid;
    
    public $remindedon;
}
?>