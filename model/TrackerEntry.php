<?php

class TrackerEntry extends Bindable
{
    public $id;
	public $user;
	public $url;
	public $actionname;
	public $actionid;
	public $remoteip;
	public $created;
	public $entrydata;
	
	public $constraints;
	public $requiredFields = array('user');
	
	public function __construct()
	{
	    $this->created = date('Y-m-d H:i:s', time());
	    $this->constraints['actionname'] = new Zend_Validate_Regex('/^[A-Za-z0-9:\-\+_]+$/');
		
	    $this->remoteip = ifset($_SERVER, 'HTTP_X_REAL_IP', ifset($_SERVER, 'REMOTE_ADDR', ''));
	}
}

?>