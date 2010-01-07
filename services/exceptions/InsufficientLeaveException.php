<?php

class InsufficientLeaveException extends Exception
{
    public $available;
    public $desired;
    
    public function __construct($available, $desired)
    {
        $this->available = $available;
        $this->desired = $desired;
    }
}
?>