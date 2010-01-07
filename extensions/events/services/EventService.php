<?php

include_once 'extensions/events/model/Invitee.php';

class EventService
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
	 * The user service
	 *
	 * @var UserService
	 */
	public $userService;
	
    /**
	 * @var ClientService
	 */
	public $clientService;
	
	/**
	 * The authentication service, IOC'd in
	 *
	 * @var DbAuthService
	 */
	public $authService;
    
    /**
     * @return Event
     */
    public function getEvent($id, $associations=false)
    {
        $event = $this->dbService->getById($id, 'Event');
        
        if ($associations) {
            $select = $this->dbService->select();
            /* @var $select Zend_Db_Select */
            $select->from('eventuser')
                ->joinInner('invitee', 'invitee.eventuserid=eventuser.id', 'uid')
                ->where('invitee.eventid=?', $id)
                ->order('eventuser.firstname asc');
            
            $invitees = $this->dbService->fetchObjects('EventUser', $select);
            $newList = new ArrayObject();
            
            foreach ($invitees as $invitee) {
                $newList[$invitee->id] = $invitee;
            }
            
            $event->setInvitees($newList);
            
            $select = $this->dbService->select();
            /* @var $select Zend_Db_Select */
            $select->from('eventuser')
                ->joinInner('attendee', 'attendee.eventuserid=eventuser.id', 'eventuserid')
                ->joinLeft('client', 'attendee.refererid=client.id', 'title as referer')
                ->where('attendee.eventid=?', $id)
                ->order('eventuser.firstname asc');
            
            $attendees = $this->dbService->fetchObjects('EventUser', $select);
            
            $newList = new ArrayObject();
            
            foreach ($attendees as $attendee) {
                $newList[$attendee->id] = $attendee;
            }

            $event->setAttendees($newList);
            
        }

        return $event;
    }
    
    /**
     * Save an event
     */
    public function saveEvent(Event $event)
    {
        $this->dbService->saveObject($event);
    }
    
    /**
     * Get events, by default those whose date is only in the future
     */
    public function getEvents($includeOld = false, $getPrivate = false)
    {
        $options = array();
        if (!$includeOld) {
            $options['eventdate > '] = date('Y-m-d H:i:s', time());
        }
        
        if (!$getPrivate) {
            $options['ispublic ='] = 1;
        }

        return $this->dbService->getObjects('Event', $options, 'eventdate asc');
    }
    
    /**
     * Get all the events that are available to be invited
     */
    public function getInvitableEvents()
    {
        $where = array(
            'inviteon < ' => date('Y-m-d H:i:s'),
            new Zend_Db_Expr('invitedate IS NULL'),
        );
        
        $events = $this->dbService->getObjects('Event', $where);

        $fullyLoadedEvents = new ArrayObject();
        foreach ($events as $event) {
            $loadedEvent = $this->getEvent($event->id, true);
            $fullyLoadedEvents[] = $loadedEvent;
        }
        
        return $fullyLoadedEvents;
    }
    
    /**
     * Get events that can be reminded. These are those whose
     * invitedate IS NOT NULL and 
     * reminderdate IS NULL and
     * today + 1 week > eventdate 
     */
    public function getRemindableEvents()
    {
        $where = array(
            'eventdate < ' => date('Y-m-d H:i:s', strtotime("+1 week")),
            new Zend_Db_Expr('invitedate IS NOT NULL'),
            new Zend_Db_Expr('reminderdate IS NULL'),
        );
        
        $events = $this->dbService->getObjects('Event', $where);

        $fullyLoadedEvents = new ArrayObject();
        foreach ($events as $event) {
            $loadedEvent = $this->getEvent($event->id, true);
            $fullyLoadedEvents[] = $loadedEvent;
        }
        
        return $fullyLoadedEvents;
    }

    /**
     * Creates an event user from a bunch of parameters passed through
     * 
     * These parameters are a map of user or event properties 
     * 
     * @return EventUser
     */
    public function createEventUser($params, $notify = true)
    {
        $user = null;
        try {
		    $this->dbService->beginTransaction();
		    
		    // Get a current contact object for the given email. If it exists,
            // we'll assume this is that person. 
            $contact = $this->updateContactDetails($params);
		    /*$contact = $this->clientService->getContactByField('email', ifset($params, 'email'));

		    if ($contact == null) {
		        $this->log->debug("Creating new contact for ".$params['email']);
		        $contact = $this->clientService->saveContact($params);
		        $companyName = trim(ifset($params, 'company')); 
                $client = $this->clientService->getClientByField(array('title'=>$companyName)); 
		        if ($client) {
		            $contact->clientid = $client->id;
		            $this->clientService->saveContact($contact);
		        } else if (mb_strlen($companyName)) {
                    // Create a new client
                    $client = $this->clientService->saveClient(array('title' => $companyName));
                    $contact->clientid = $client->id;
		            $this->clientService->saveContact($contact);
		        }
		    }*/

		    $this->log->debug("Creating new user for ".ifset($params, 'email'));
		    $params['username'] = ifset($params, 'email');
		    $params['password'] = mt_rand(1000, 9999);
		    
			$user = $this->userService->createUser($params, $notify, User::ROLE_PUBLIC, 'EventUser');

			// Update with the correct contact id
			$user->contactid = $contact->id;
			$user->role = User::ROLE_PUBLIC;
			$this->userService->saveUser($user);

			// Reset the user's password
            try {
       			$this->authService->resetPassword(ifset($params, 'email'), $notify);
            } catch (Exception $e) {
                $this->log->warn("Failed resetting password");
            }
			
			// now check if there was an event involved while signing up
			if (ifset($params, 'eventid')) {
			    $this->log->debug("Registering user for event");
			    $event = $this->getEvent(ifset($params, 'eventid')); 
			    // add the user to this event
                $this->addAttendee($user, $event);
			}
			
			$this->log->debug("Committing new user");
            $this->dbService->commit();
          
		} catch (ExistingAttendeeException $eae) {
		    $this->dbService->rollback();
		    throw $e;
		} catch (Exception $e) {
			$this->dbService->rollback();
			$this->log->err(get_class($e).": Failed creating new user: ".$e->getMessage());
			throw $e; 
		}
		
		return $user;
    }
    
    /**
     * Updates the contact details stored for the given
     * email address and company details.
     */
    public function updateContactDetails($params)
    {
        $contact = $this->clientService->getContactByField('email', ifset($params, 'email'));

	    if ($contact == null) {
	        $this->log->debug("Creating new contact for ".$params['email']);
	        $contact = $this->clientService->saveContact($params);
	    } else {
	        $contact->bind($params);
	    }
    
        $companyName = trim(ifset($params, 'company')); 
        $client = $this->clientService->getClientByField(array('title'=>$companyName)); 
        if ($client) {
            $contact->clientid = $client->id;
        } else if (mb_strlen($companyName)) {
			// Create a new client
			$client = $this->clientService->saveClient(array('title' => $companyName));
			$contact->clientid = $client->id;
        }

	    $this->clientService->saveContact($contact);
	    
	    return $contact;
    }

    /**
     * Get the invitation object for the corresponding uid that was generated
     * for it when the invitation was made. 
     * 
     * @return Invitee
     */
    public function getInviteFromUid($uid)
    {
        return $this->dbService->getByField(array('uid'=>$uid), 'Invitee');
    }
    
    /**
     * Add an attendee to an event
     * 
     * @param EventUser $user The user to add
     * @param Event $event The event to add the user to
     */
    public function addAttendee(EventUser $user, Event $event, $referid=0)
    {
        if ($user->id == null || $event->id == null) {
            throw new Exception('Invalid event data specified'); 
        }
        // See if this person is already attending
        $users = $this->dbService->getObjects('Attendee', array('eventid=' => $event->id, 'eventuserid=' => $user->id));
        if (count($users) > 0) {
            // woah !
            throw new ExistingAttendeeException("User ".$user->username." is already registered for this event");
        }
        
        if (!$referid) {
            $referid = za()->getSession()->referer;
        }
        
        $attendee = new Attendee();
        $attendee->eventid = $event->id;
        $attendee->eventuserid = $user->id;
        $attendee->refererid = $referid;
        
        // save away
        $this->dbService->saveObject($attendee);

    }
    
    public function deleteEvent(Event $event)
    {
        // delete from attendee and invitee too
        $this->dbService->beginTransaction();
        $this->dbService->delete('Invitee', array('eventid=?' => (int) $event->id));
        $this->dbService->delete('Attendee', array('eventid=?' => (int) $event->id));
        $this->dbService->delete($event);
        $this->dbService->commit();
    }
    
    /**
     * Remove an attendee from an event
     */
    public function removeAttendee(EventUser $user, Event $event)
    {
        if ($user->id == null || $event->id == null) {
            throw new Exception('Invalid event data specified'); 
        }
        
        $attendees = $this->dbService->getObjects('Attendee', array('eventid=' => $event->id, 'eventuserid=' => $user->id));
        if (count($attendees) == 0) {
            // woah !
            throw new Exception("Event not found");
        }
        
        foreach ($attendees as $attendee) {
            $this->dbService->delete($attendee);
        }
    }

    /**
     * Gets all the users that can be added to an event
     * @return EventUser
     */
    public function getEventUser($id)
    {
        return $this->dbService->getById($id, 'EventUser');
    }
    
    public function getEventUserByUid($uid)
    {
        return $this->dbService->getByField(array('useruid'=>$uid), 'EventUser');
    }
    
    public function saveEventUser(EventUser $user)
    {
        $this->dbService->saveObject($user);
    }
    
    /**
     * Add a user to an event
     */
    public function addInvitee(Event $event, EventUser $user)
    {
        $invitee = new Invitee();
        
        // check to see if the user is already invited
        $list = $event->getAttendees();
        if ($list != null && $list->offsetExists($user->id)) {
            // They're already here
            return;
        }
        
        $invitee->eventid = $event->id;
        $invitee->eventuserid = $user->id;
        
        $this->dbService->saveObject($invitee);
    }
    
    /**
     * Remove an invitee from an event
     */
    public function removeInvitee(EventUser $user, Event $event)
    {
        if ($user->id == null || $event->id == null) {
            throw new Exception('Invalid event data specified'); 
        }
        
        $invitees = $this->dbService->getObjects('Invitee', array('eventid=' => $event->id, 'eventuserid=' => $user->id));
        if (count($invitees) == 0) {
            // woah !
            throw new Exception("Event not found");
        }
        
        foreach ($invitees as $invitee) {
            $this->dbService->delete($invitee);
        }
    }
    
    /**
     * Gets all the users that can be added to an event
     * 
     * @param $event
     * 			If the event is not null, then we only want those that aren't
     * 			already invited to the event
     */
    public function getEventUsers($event=false)
    {
    	if ($event != null) {
    		$sql = 'select * from eventuser where id not in (select eventuserid from invitee where eventid='.$this->dbService->quote($event->id).') order by firstname asc';
            return $this->dbService->fetchObjects('EventUser', $sql);
    	} else {
        	return $this->dbService->getObjects('EventUser', array(), 'firstname asc');
    	}
    }
    
    /**
     * Get all the locations stored for events
     */
    public function getEventLocations()
    {
        $query = 'select distinct(location) from event';
        $result = $this->dbService->query($query);
        /* @var $result Zend_Db_Statement_Pdo */
        $res = $result->fetchAll(PDO::FETCH_COLUMN);
        return $res;
    }
    
    /**
     * Preview the emails for a given event
     */
    public function previewEventEmails(Event $event, $email)
    {
        $user = new User();
        $user->email = $email;
        set_time_limit(0);

        $this->notificationService->notifyUser("Event invite preview", $user, $event->prepareEmail('inviteemail', $user), za()->getConfig('from_email'), "No Reply", true);
        $this->notificationService->notifyUser("Event last-chance preview", $user, $event->prepareEmail('lastchanceemail', $user), za()->getConfig('from_email'), "No Reply", true);
        $this->notificationService->notifyUser("Event reminder preview", $user, $event->prepareEmail('reminderemail', $user), za()->getConfig('from_email'), "No Reply", true);
    }
    
}

class ExistingAttendeeException extends Exception {}
?>