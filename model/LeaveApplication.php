<?php

class LeaveApplication extends MappedObject
{
    const LEAVE_APPROVED = 'Approved';
    const LEAVE_DENIED = 'Denied';
    const LEAVE_PENDING = 'Pending';
    
    public $username;
    public $from;
    public $to;
    
    /**
     * How many days does the user want?
     *
     * @var int
     */
    public $numdays;
    
    /**
     * How many days should be deducted from a user's leave
     * total once this application is approved? This is decided by the approver
     */
    public $days = 0;

    public $approver;

    public $status = self::LEAVE_PENDING;

    public $reason;
    
    public $constraints = array();
    
    public $requiredFields = array('username', 'from', 'to', 'reason');
    
    public $leavetype;
    
    public function __construct()
    {
        $this->constraints['__this'] = new ValidPeriodValidator();
        $this->created = date('Y-m-d H:i:s', time());
        $this->constraints['leavetype'] = new CVLValidator(array('Annual', 'Sick', 'Long Service'));
    }
}

class ValidPeriodValidator implements Zend_Validate_Interface 
{
    private $messages = array();
    
    public function isValid($model)
    {
        $from = strtotime($model->from);
        $to = strtotime($model->to);
        
        if ($from > $to) {
            $this->messages[] = "From date must be before To date";
            return false;
        }
        return true;
    }
    
    /**
	* Get validation erro messages
	 *
	 * @return array
	 */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
	 * Get validation erro messages
	 *
	 * @return array
	 */
    public function getErrors()
    {
        return $this->getMessages();
    }
}
?>