<?php

class Event extends Bindable
{
    public $id;
    public $created;
    public $updated;

    public $title;
    public $eventdate;
    
    public $starttime;
    public $endtime;

    public $location;
    public $description;
    
    public $postevent;
    
    public $maxattendees;
    
    public $inviteon;
    
    public $inviteemail;
    public $lastchanceemail;
    public $reminderemail;
    
    public $invitedate;
    public $lastchancedate;
    public $reminderdate;

    public $ispublic;
    
    private $invitees = array();
    private $attendees = array();
    
    /**
     * @var unmapped
     */
    public $notificationService;
    
    /**
     * @var unmapped
     */
    public $dbService;
    
    public $requiredFields = array('title');
    
    /**
     * Prepares the named email for sending to the passed in user
     *
     * can be either inviteemail|lastchanceemail|reminderemail
     * 
     * @param string $emailName
     * @param EventUser $sendTo
     */
    public function prepareEmail($emailName, $sendTo)
    {
        $emailText = $this->$emailName;
        if (!mb_strlen($emailText)) {
            return '';
        }

        $toReplace = array('{firstname}', '{lastname}', '{responseurl}', '{dontspamme}');
        
        $registerParams = array();
        
        // if there's a uid for this user (representing the invitation for a 
        // particular event)
        if (isset($sendTo->uid)) {
            $registerParams['inviteid'] = $sendTo->uid;
        }

        $replaceWith = array(
            $sendTo->firstname, 
            $sendTo->lastname, 
            build_url('event', 'emailregister', $registerParams, true),
            build_url('event', 'unsubscribe', array('useruid'=>$sendTo->useruid), true),
        );
        // need to replace some things
        $rep = str_replace($toReplace, $replaceWith, $emailText);
        return $rep;
    }
    
    /**
     * Prepare the description for frontend viewing
     */
    public function prepareDescription()
    {
        $replace = array(
            '{posteventurl}',
        );
        $with = array(
            build_url('event', 'postevent', array('id'=>$this->id)),
        );
        
        return str_replace($replace, $with, $this->description);
    }

    
    /**
     * Invite the users selected for invitation to this event
     */
    public function invite()
    {
        set_time_limit(0);
        foreach ($this->invitees as $invitee) {
            // check to see if this invitee has already been invited
            $invite = $this->dbService->getByField(array('eventuserid'=>$invitee->id, 'eventid' => $this->id), 'Invitee');
            if ($invite == null) {
                continue;
            }
            if ($invite->invitedon != null) {
                $this->log->warn("User ".$invitee->username." has already been invited on ".$invite->invitedon);
            } else {
                $this->log->debug("Inviting invitee ".$invitee->username);
	            // Only invite if they're subscribed AND not already attending
	            if ($invitee->subscribed && !isset($this->attendees[$invitee->id])) {
	                $this->notificationService->notifyUser("Event invitation", $invitee, $this->prepareEmail('inviteemail', $invitee), za()->getConfig('from_email'), "No Reply", true);
	            }
	            $invite->invitedon = date('Y-m-d H:i:s');
                $this->dbService->saveObject($invite);
            }
        }
    }
    
    /**
     * Remind all invited users that haven't yet registered
     */
    public function remindInvitees()
    {
        set_time_limit(0);
        foreach ($this->invitees as $invitee) {
            // check to see if this invitee has already been invited
            $invite = $this->dbService->getByField(array('eventuserid'=>$invitee->id, 'eventid' => $this->id), 'Invitee');
            if ($invite == null) {
                continue;
            }
            if ($invite->remindedon != null) {
                $this->log->warn("User ".$invitee->username." has already been reminded on ".$invite->remindedon);
            } else {
                
	            // Only invite if they're subscribed AND not already attending
	            if ($invitee->subscribed && !isset($this->attendees[$invitee->id])) {
	                $this->log->debug("Reminding invitee ".$invitee->username);
	                $this->notificationService->notifyUser("Event reminder", $invitee, $this->prepareEmail('lastchanceemail', $invitee), za()->getConfig('from_email'), "No Reply", true);
	            }
	            $invite->remindedon = date('Y-m-d H:i:s');
                $this->dbService->saveObject($invite);
            }
        }
    }
    
    /**
     * Send a reminder email to all REGISTERED users
     */
    public function remindAttendees()
    {
        set_time_limit(0);
        foreach ($this->attendees as $attendee) {
            // check to see if this invitee has already been invited
            $registration = $this->dbService->getByField(array('eventuserid'=>$attendee->id, 'eventid' => $this->id), 'Attendee');
            if ($registration == null) {
                continue;
            }
            if ($registration->remindedon != null) {
                $this->log->warn("User ".$attendee->username." has already been reminded on ".$registration->remindedon);
            } else {
	            // Only remind if they're subscribed
                
	            if ($attendee->subscribed) {
	                $this->log->debug("Reminding attendee ".$attendee->username);
	                $this->notificationService->notifyUser("Event reminder", $attendee, $this->prepareEmail('reminderemail', $attendee), za()->getConfig('from_email'), "No Reply", true);
	            }
	            $registration->remindedon = date('Y-m-d H:i:s');
                $this->dbService->saveObject($registration);
            }
        }
    }

    public function getInvitees()
    {
        return $this->invitees;
    }
    
    public function getAttendees()
    {
        return $this->attendees;
    }
    
    public function setInvitees($value)
    {
        $this->invitees = $value;
    }
    
    public function setAttendees($value)
    {
        $this->attendees = $value;
    }
}
?>