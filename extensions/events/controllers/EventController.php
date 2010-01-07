<?php

class EventController extends NovemberController
{
    /**
     * @var EventService
     */
    public $eventService;
    
    /**
     * @var ClientService
     */
    public $clientService;
    
    /**
     * @var AuthService
     */
    public $authService;
    
    public $allowedMethods = array(
        'unregisterAction' => 'post',
		'removeattendeeAction' => 'post',
		'deleteAction' => 'post',
		'sendinvitesAction' => 'get',
		'importusersAction' => 'post',
	);
    
    public function indexAction()
    {
        
        // if it's a frontend user, go to the list 
        if (za()->getUser()->hasRole(User::ROLE_USER)) {
            $events = $this->eventService->getEvents(true, true);
            $this->view->items = $events;
            
            $this->renderView('event/index.php');
        } else { 
            $this->redirect('event', 'list');
        }
    }

    public function listAction()
    {
        $getPrivate = false;
        if (za()->getUser()->hasRole(User::ROLE_USER)) {
            $getPrivate = true;
        }
        $events = $this->eventService->getEvents(false, $getPrivate);
        $this->view->items = $events;

        $this->renderView('event/list.php');
    }
    
    public function deleteAction()
    {
        $event = $this->byId();
        $this->eventService->deleteEvent($event);
        $this->redirect('event');
    }
    
    public function viewAction()
    {
        $this->view->model = $this->eventService->getEvent($this->_getParam('id'), true);
        // We'll cheat and stick the eventid into the request, because we know
        // that it can be used later
        if (!za()->getUser()->hasRole(User::ROLE_PUBLIC)) {
            if (!$this->view->model->ispublic) {
                $this->flash("Event ".$this->view->model->id." is not publically accessible (".$this->view->model->ispublic.")");
                $this->redirect('event', 'list');
                return;
            }
        }
        $this->prepareForView($this->view->model);
        $this->renderView($this->_request->getControllerName().'/view.php');
    }
    
    public function postEventAction()
    {
        $this->view->model = $this->eventService->getEvent($this->_getParam('id'), true);
        // We'll cheat and stick the eventid into the request, because we know
        // that it can be used later
        if (!za()->getUser()->hasRole(User::ROLE_PUBLIC)) {
            if (!$this->view->model->ispublic) {
                $this->flash("Event ".$this->view->model->id." is not publically accessible (".$this->view->model->ispublic.")");
                $this->redirect('event', 'list');
                return;
            }
        }
        $this->prepareForView($this->view->model);
        $this->renderView($this->_request->getControllerName().'/postevent.php');
    }
    
    public function addattendeeAction()
    {
    	$event = $this->eventService->getEvent($this->_getParam('eventid')); 
    	$user = $this->eventService->getEventUser($this->_getParam('eventuserid'));
    	
    	$this->eventService->addAttendee($user, $event, 0);
    	$this->redirect('event', 'edit', array('id'=>$event->id, '#invitees'));
    }
    
    public function registerAction()
    {
        $event = $this->eventService->getEvent($this->_getParam('id'));
        $user = za()->getUser();
        $referer = za()->getSession()->referer;
        $this->eventService->addAttendee($user, $event, $referer);
        $this->redirect('event', 'view', array('id' => $event->id));
    }
    
    public function unRegisterAction()
    {
        $event = $this->eventService->getEvent($this->_getParam('id'));
        $user = za()->getUser();
        
        $this->eventService->removeAttendee($user, $event);
        $this->redirect('event', 'view', array('id' => $event->id));
    }

    /**
     * Get the model object
     */
    protected function getModel($fullyLoad = false)
    {
        return $this->eventService->getEvent($this->_getParam('id'), $fullyLoad);
    }
    
    protected function saveObject($params, $modelType)
    {
        return $this->dbService->saveObject($params, $modelType);
    }
    
    public function prepareForEdit($model)
    {
        $this->view->locations = $this->eventService->getEventLocations();
        parent::prepareForEdit($model);
    }
    
    
    public function listAttendeesAction()
    {
        $this->view->model = $this->getModel(true);
        $this->renderView('event/attendeeslist.php');
    }
    
    public function listInviteesAction()
    {    
    	$this->view->model = $this->getModel(true);
        $this->view->userList = $this->eventService->getEventUsers($this->view->model);
        
        $this->renderView('event/inviteeslist.php');
    }
    
    
    public function addInviteeAction()
    {
        $people = $this->_getParam('people', array());
        $event = $this->eventService->getEvent($this->_getParam('id'));
        
        if ($event == null) {  
            $this->flash("Invalid event");
            $this->indexAction();
            return;
        }

        foreach ($people as $personid) {
            $user = $this->eventService->getEventUser($personid);
            // now add them to the event
            $this->eventService->addInvitee($event, $user);
        }
        
        $this->redirect('event', 'edit', array('id'=>$event->id, '#invitees'));
    }
    
    public function deleteInviteeAction()
    {
        $people = $this->_getParam('people', array());
    }

    public function removeAttendeeAction()
    {
        $event = $this->eventService->getEvent($this->_getParam('eventid'));
        $user = $this->eventService->getEventUser($this->_getParam('eventuserid'));
        
        $this->eventService->removeAttendee($user, $event);
        $this->redirect('event', 'edit', array('id' => $event->id, '#attendees'));
    }
    
    public function removeInviteeAction()
    {
        $event = $this->eventService->getEvent($this->_getParam('eventid'));
        $user = $this->eventService->getEventUser($this->_getParam('eventuserid'));
        
        $this->eventService->removeInvitee($user, $event);
        $this->redirect('event', 'edit', array('id' => $event->id, '#invitees'));
    }

    public function previewemailAction()
    {
        $event = $this->eventService->getEvent($this->_getParam('id'));
        $target = $this->_getParam('sendto');
        
        $this->eventService->previewEventEmails($event, $target);
        $this->redirect('event', 'edit', array('id' => $event->id, '#invitemail'));
    }
    
    /**
     * Register for an event from an email click
     */
    public function emailRegisterAction()
    {
        $invite = $this->eventService->getInviteFromUid($this->_getParam('inviteid'));
        if ($invite == null) {
            $this->error("Invalid invitation");
            return;
        }
        
        $event = $this->eventService->getEvent($invite->eventid);
        $user = $this->eventService->getEventUser($invite->eventuserid);

        if ($user == null || $event == null) {
            $this->error("Invalid user or event specified");
            return;
        }
        
        // Set this user as being logged in, as they're coming from an email!
        $this->authService->setAuthenticatedUser($user);
        $referer = za()->getSession()->referer;
        $this->eventService->addAttendee($user, $event, $referer);
        $this->flash("You have registered for ".$event->title);
        $this->redirect('event', 'view', array('id' => $event->id));
    }

    public function unsubscribeAction()
    {
        $user = $this->eventService->getEventUserByUid($this->_getParam('useruid'));
        
        if ($user == null) {
            $this->error("Invalid user specified");
            return;
        }
        
        // Set this user as being logged in, as they're coming from an email!
        $this->authService->setAuthenticatedUser($user);

        $user->subscribed = 0;
        $this->eventService->saveEventUser($user);
        $this->flash("Unsubscribed");
        $this->redirect('event', 'list');
    }
    
    /**
     * DEBUG METHOD ONLY FOR NOW
     */
    public function sendinvitesAction()
    {
        $event = $this->eventService->getEvent($this->_getParam('id'), true);
        
        $this->redirect('event', 'edit', array('id' => $event->id)); 
    }
    
    public function importAction()
    {
        $this->renderView('event/import-eventusers.php');
    }
    
    public function importusersAction()
    {
        if (!isset($_FILES['import']) && !isset($_FILES['import']['tmp_name'])) {
            throw new Exception("Import file not found");
        }

        $fname = $_FILES['import']['tmp_name'];
        $contacts = null;
        try {
            $this->dbService->beginTransaction();
            try {
                $contacts = $this->clientService->importContacts($fname);
            } catch (ContactImportException $cie) {
                $contacts = $cie->imported;
	            $msg = array("Imported ".count($contacts)." contacts, ".count($cie->errors)." not imported.", $cie->errors);
	            $this->flash($msg);
            }

            $this->log->debug("Creating event users for ".count($contacts)." contacts");
            // Now create event users
            foreach ($contacts as $contact) {
                $params = array(
                    'email' => $contact->email,
                    'firstname' => $contact->firstname,
                    'lastname' => $contact->lastname,
                );
                // Try creating the user, it may fail!
                try {
                    $this->eventService->createEventUser($params, false);
                } catch (InvalidModelException $ime) {
                    // ignore it
                    $this->log->warn("Failed creating event user for contact ".$contact->email);
                }
            }

            $this->dbService->commit(); 
        } catch (InvalidModelException $invalidmodelexception) {
            $this->dbService->rollback();
            $this->log->err("Failed importing event users: ".$invalidmodelexception->getMessage());
            $this->flash($invalidmodelexception->getMessages());
            $this->redirect('event', 'import');
            return;
        } catch (Exception $e) {
            $this->dbService->rollback();
            $this->log->err("Failed importing event users: ".$e->getMessage());
            $this->flash($e->getMessage());
            $this->redirect('event', 'import');
            return;
        }
        
        $this->redirect('event');
    }
}

?>