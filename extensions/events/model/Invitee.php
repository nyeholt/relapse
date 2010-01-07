<?php

class Invitee extends Bindable 
{
    public $id;
    public $created;
    public $updated;
    
    public $eventid;
    public $eventuserid;
    
    public $invitedon;

    public $remindedon;
    
    /**
     * A unique id so that this invitation is uniquely identifiable
     *
     * @var unknown_type
     */
    public $uid;
    
    public function __construct()
    {
        $this->uid = md5(uniqid(rand(),1));
    }
}
?>