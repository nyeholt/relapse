<?php
class Invoice extends Bindable 
{
    public $id;
    public $created;
    public $updated;
    
    public $title;
    
    public $timesheetid;
    public $projectid;
    
    public $amountpaid = 0;
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
        $this->to = $this->created;
    }
}
?>