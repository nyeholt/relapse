<?php

class NoRouteController extends NovemberController 
{
	public function indexAction()
	{
	    $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');
		echo "<h1>404 not found</h1>";
		
		$this->log->debug("Could not find a handler for ".$_SERVER['PHP_SELF'], Zend_Log::LEVEL_ERROR);
	}
}

?>