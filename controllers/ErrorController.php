<?php
class ErrorController extends NovemberController  
{
    public function indexAction()
    {
        $this->renderView('error.php');
    }
    
    public function errorAction()
    {
        $error = $this->_getParam('error_handler');
        $this->view->message = $error['exception']->getMessage();
        $this->log->err($error['exception']->getTraceAsString());
        $this->renderView('error.php');
    }
}
?>