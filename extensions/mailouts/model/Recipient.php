<?php
class Recipient
{
    public $id;
    public $mailid;
    public $userid;
    public $uid;

    /**
     * When was this email sent?
     */
    public $mailedon;
    
    public function __construct()
    {
        $this->uid = md5(uniqid(rand(),1));
    }
}
?>