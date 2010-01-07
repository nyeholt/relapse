<?php

include_once dirname(__FILE__).'/lib/pop3.phpclasses.php';

/**
 * This acts as a proxy to the POP3 library
 *
 */
class PopService
{
    private $proxied;
    
    public function __construct()
    {
        // create a proxy
        $this->proxied = new pop3_class;
    }
    
    public function __call($m, $a)
    {
        if (method_exists($this->proxied, $m)) {
            return call_user_func_array(array($this->proxied, $m), $a);
        }
        
        throw new Exception('Method '.$m.' not defined in '.get_class($this->proxied));
    }

    public function __set($k, $v)
    {
        if ($k == 'log') {
            return;
        }
        return $this->proxied->$k = $v;
    }
    
    public function __get($k)
    {
        return $this->proxied->$k;
    }
    
}
?>