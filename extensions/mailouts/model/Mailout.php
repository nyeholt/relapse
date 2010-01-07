<?php
include_once dirname(__FILE__).'/Recipient.php';

class Mailout extends Bindable
{
    public $id;
    public $title;
    public $created;
    public $updated;

    public $tomail;
    public $maildate;
    public $html;
    
    public $requiredFields = array('title');

    /**
     * @var unmapped
     */
    public $dbService;
    
    /**
     * @var unmapped
     */
    public $notificationService;
    
    private $recipients;
    
    public function getRecipients()
    {
        if ($this->recipients == null) {
            // get everything from eventuser where there's an entry in
            // recipient
            $where = array('mailid=' => $this->id);
            $select = $this->dbService->select();
            $select->from('eventuser')
                ->joinInner('recipient', 'recipient.userid=eventuser.id', 'recipient.uid');
            
            $this->dbService->applyWhereToSelect($where, $select);
            $recipients = $this->dbService->fetchObjects('EventUser', $select);
            
            
            $this->recipients = array();
            foreach ($recipients as $recipient) {
            	// store keyed by ID so we can filter later
				$this->recipients[$recipient->id] = $recipient;
            }
        }

        return $this->recipients;
    }
    
    /**
     * Add a recipient for this mailout
     */
    public function addRecipient($user)
    {
        $newRecipient = new Recipient();
        
        // check to see if the user is already invited
        $list = $this->getRecipients();

        foreach ($list as $recipient) {
            if ($recipient->id == $user->id) {
                $this->log->warn("User ".$user->id." is already a recipient");
                return;
            }
        }

        $newRecipient->mailid = $this->id;
        $newRecipient->userid = $user->id;
        $saved = $this->dbService->saveObject($newRecipient);
        $this->log->debug("Added user ".$user->username." to ".$this->title." with recipient id of ". ($saved != null ? $saved->id : 'NULL'));
    }
    
    /**
     * Remove a recipient from this email
     */
    public function removeRecipient($user)
    {
        $recipients = $this->dbService->getObjects('Recipient', array('userid='=>$user->id, 'mailid=' => $this->id));
        
        if (count($recipients) == 0) {
            throw new Exception("Could not find recipient");
        }
        foreach ($recipients as $recipient) {
            $this->dbService->delete($recipient);
        }
    }
    
/**
     * Prepares the named email for sending to the passed in user
     *
     * 
     * @param EventUser $sendTo
     */
    protected function prepareEmail($sendTo)
    {
        $emailText = $this->html;
        
        if (!mb_strlen($emailText)) {
            return '';
        }

        $toReplace = array('{firstname}', '{lastname}', '{dontspamme}');
        
        $spamUrl = '';
        if (isset($sendTo->useruid)) {
        	// use the configured 'unsubscribe' url. 
			$spamUrl = za()->getConfig('unsubscribe_domain');
			if ($spamUrl == null) {
				$spamUrl = build_url('event', 'unsubscribe', array('useruid'=>$sendTo->useruid), true);
			} else {
				$spamUrl = za()->getConfig('unsubscribe_domain') . '/event/unsubscribe/useruid/'.$sendTo->useruid;
			}
        }

        $replaceWith = array(
            $sendTo->firstname, 
            $sendTo->lastname, 
            $spamUrl,
        );
        // need to replace some things
        $rep = str_replace($toReplace, $replaceWith, $emailText);
        return $rep;
    }
    
    /**
     * Send a preview of this email
     */
    public function preview($email)
    {
        // get the email
        $text = $this->prepareEmail(za()->getUser());
        // now send

        $user = new User();
        $user->email = $email;
        $this->notificationService->notifyUser("Mailout preview", $user, $text, za()->getConfig('from_email'), "No Reply", true);
    }

    /**
     * Send the mailout
     */
    public function sendMailout()
    {
        $recipients = $this->getRecipients();
        set_time_limit(0);
        foreach ($recipients as $recipient) {
            // check to see if this recipient has already been invited
            $mailInvite = $this->dbService->getByField(array('userid'=>$recipient->id, 'mailid' => $this->id), 'Recipient');
            if ($mailInvite == null) {
                // No invitation found for the supposed recipient!
                continue;
            }
            if ($mailInvite->mailedon != null) {
                $this->log->warn("User ".$recipient->username." has already been emailed on ".$mailInvite->mailedon);
            } else {
                $this->log->debug("Sending mailout to ".$recipient->username);
	        
	            if ($recipient->subscribed) {
	                // Try sending, if so we mark them as recieving
                    try {
    	                $this->notificationService->notifyUser($this->title, $recipient, $this->prepareEmail($recipient), za()->getConfig('from_email'), "No Reply", true);
    	                $mailInvite->mailedon = date('Y-m-d H:i:s');
                        $this->dbService->saveObject($mailInvite);
                    } catch (Exception $e) {
                        $this->log->err("Failed sending mail to ".$recipient->username.": ".$e->getMessage());
                        $this->log->err($e->getTraceAsString());
                    }
	            } else {
	                $this->log->debug($recipient->username." is not subscribed");
	            }
            }
        }
        
        $this->maildate = date('Y-m-d H:i:s');
        $this->dbService->saveObject($this);
    }
}
?>