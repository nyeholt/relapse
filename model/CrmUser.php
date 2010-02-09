<?php

class CrmUser extends User 
{

    /**
     * When did this user start with the company?
     *
     * @var datetime
     */
    public $startdate;
    
    /**
     * A user may have a contact associated with them
     *
     * @var int
     */
    public $contactid;

    public function __construct()
    {
        parent::__construct();
        $this->startdate = date('Y-m-d h:i:s');
    }

    /**
     * Overridden to send external users a correct URL
     */
    public function getDefaultModule() 
    {

        if ($this->role == User::ROLE_EXTERNAL && $this->defaultmodule == '') {
            return 'external';
        }
        if ($this->defaultmodule == null) {
        	$this->defaultmodule = '';
        }
        return $this->defaultmodule; 
    }
}

?>