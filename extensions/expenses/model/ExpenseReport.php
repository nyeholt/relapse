<?php

class ExpenseReport extends MappedObject
{
    public $title;
    
    public $username;
    public $projectid;
    public $clientid;

    public $locked;

    public $paiddate;

    public $from;
    public $to;
    
    public $total;
    
    public $requiredFields = array('title', 'from', 'to');
    
    private $expenses = null;
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
    }

}
?>