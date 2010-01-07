<?php

class ItemLink extends Bindable 
{
    public $id;
    public $fromid;
    public $fromtype;
    public $toid;
    public $totype;
    
    private $from;
    private $to;
    
    public function setFrom($from)
    {
        $this->from = $from;
    }
    
    public function setTo($to)
    {
        $this->to = $to;
    }
    
    public function getFrom()
    {
        return $this->from;
    }
    
    public function getTo()
    {
        return $this->to;
    }
}
?>