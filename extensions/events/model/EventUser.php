<?php
class EventUser extends User
{
    public $subscribed = 1;

    public $contactid;
    
    public $useruid;
    
    public $requiredFields = array('email');
    
    public function __construct()
    {
        parent::__construct();
        $this->useruid = md5(uniqid(rand(),1));
    }
}
?>