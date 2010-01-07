<?php
class MailoutController extends NovemberController
{
    /**
     * EventService
     *
     * @var EventService
     */
    public $eventService;
    
    public function prepareForEdit($model)
    {
        // Get all the event users because they're the ones who 
        // can be emailed
		$users = $this->eventService->getEventUsers();		
		$userlist = array();
		$recipients = $model->getRecipients();
		foreach ($users as $user) {
			if (!isset($recipients[$user->id])) {
				$userlist[] = $user;
			}
		}
        $this->view->userList = $userlist;
    }
	
    protected function onModelSaved($model)
    {
        $this->redirect($this->_request->getControllerName(), 'edit', array('id'=>$model->id));
    }
    
    public function addRecipientAction()
    {
        $people = $this->_getParam('people', array());
        $mailout = $this->byId();
        
        if ($mailout == null) {  
            $this->flash("Invalid mailout");
            $this->redirect('mailout');
            return;
        }

        foreach ($people as $personid) {
            $user = $this->eventService->getEventUser($personid);
            $mailout->addRecipient($user);
        }
        
        $this->redirect('mailout', 'edit', array('id'=>$mailout->id, '#recipients'));
    }
    
    public function removeRecipientAction()
    {
        $user = $this->eventService->getEventUser($this->_getParam('recipientid'));
        $mailout = $this->byId();
        
        if ($mailout == null || $user == null) {
            $this->flash("Invalid data");
            $this->redirect('mailout');
            return;
        }
        
        $mailout->removeRecipient($user);
        $this->flash("Removed recipient ".$user->username);
        $this->redirect('mailout', 'edit', array('id' => $mailout->id, '#recipients'));
    }
    
    /**
     * preview an email
     */
    public function previewAction()
    {
        $mailout = $this->byId();
        if ($mailout == null) {
            $this->flash("Invalid mailout");
            $this->redirect('mailout');
            return;
        }
        try {
            $mailout->preview($this->_getParam('email'));
        } catch (Exception $e) {
        	$this->log->err($e->getTraceAsString());
            $this->flash($e->getMessage());
        }
        $this->redirect('mailout', 'edit', array('id' => $mailout->id));
    }
    
    /**
     * Send the email out
     */
    public function sendEmailAction()
    {
        $mailout = $this->byId();
        if ($mailout == null) {
            $this->flash("Invalid mailout");
            $this->redirect('mailout');
            return;
        }
        
        try {
            $mailout->sendMailout();
        } catch (Exception $e) {
            $this->flash($e->getMessage());
        }

        $this->redirect('mailout', 'edit', array('id' => $mailout->id));
    }
}
?>