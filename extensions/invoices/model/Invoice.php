<?php
class Invoice extends MappedObject
{
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