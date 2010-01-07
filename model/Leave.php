<?php

class Leave extends Bindable
{
    public $id;
    public $created;
    public $updated;
    
    public $username;
    public $leavetype;
    public $days;
    public $lastleavecalculation;
    
    public $constraints = array();
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s', time());
	    $this->lastleavecalculation = $this->created;
	    $this->constraints['leavetype'] = new CVLValidator(array('Annual', 'Sick', 'Long Service'));
    }
}

?>