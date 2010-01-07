<?php

class Expense extends Bindable
{
    const APPROVED = 'Approved';
    const PENDING = 'Pending';
    const DENIED = 'Denied';
    
    public $id;
    public $created;
    public $updated;
    
    public $expensereportid;
    public $userreportid;
    
    public $username;
    public $approver;
    public $status = self::PENDING;
    public $location;
    public $amount;
    public $description;
    public $expensedate;
    public $paiddate;
    public $clientid;
    public $projectid;
    public $atocategory;
    public $expensetype;
    public $expensecategory;
    public $gst;


    private $projectTitle;
    private $clientTitle;
    private $firstname;
    private $lastname;

    public $constraints = array();
    public $requiredFields = array('username', 'amount', 'clientid', 'projectid');

    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
        $this->constraints['atocategory'] = new CVLValidator(array('Billable','Non-Billable'));
        $this->constraints['expensetype'] = new CVLValidator(array('Cash','Company Credit Card','Personal Credit Card'));
        $this->constraints['expensecategory'] = new CVLValidator(array('Transport','Accomodation','Meal','Stationary','Software','Hardware','Other'));
    }
    
    public function setProjectTitle($val)
    {
        $this->projectTitle = $val;
    }

    public function getProjectTitle()
    {
        return $this->projectTitle;
    }
    
    public function setClientTitle($val)
    {
        $this->clientTitle = $val;
    }
    
    public function getClientTitle()
    {
        return $this->clientTitle;
    }
    
    public function setFirstName($v)
    {
        $this->firstname = $v;
    }
    
    public function setLastName($v)
    {
        $this->lastname = $v;
    }
    
    public function getFirstName()
    {
        return $this->firstname;
    }
    
    public function getLastName()
    {
        return $this->lastname;
    }

    public function getFiles()
    {
        $expenseService = za()->getService('ExpenseService');
        $files = $expenseService->getExpenseFiles($this);

        return $files;
    }
    
    

    
}

?>