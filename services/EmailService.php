<?php

include_once dirname(__FILE__).'/lib/mimeDecode.php';

class EmailService
{
    public $popService;
    
    /**
     * Get the emails from a given server 
     *
     * @param string $server
     * @param string $user
     * @param string $pass
     * @param boolean $delete whether to delete the emails after
     *                          retrieval
     */
    public function readEmailFrom($server, $user, $pass, $delete=true, $secure=false)
    {
        $parts = split(":", $server);
        $port = "110";
        
        if (count($parts) == 2) {
            $port = $parts[1];
        }
        
        if ($port == "995") {
            $secure = true;
        }

        $server = $parts[0];

        // Connect to mail server
        include_once dirname(__FILE__).'/lib/pop3.phpclasses.php';
        $pop3 = new pop3_class;
        $pop3->hostname = $server;
        $pop3->port = $port;
        // $pop3->join_continuation_header_lines=1;
        
        if ($secure) $pop3->tls = 1;
        
        if (($error = $pop3->open()) != "") {
            throw new Exception($error);
        }
        
        if (($error = $pop3->login($user, $pass)) != "") {
            throw new Exception($error);
        }

        $messageInfo = $pop3->ListMessages("",0);
        $pop3->Statistics($messages,$size);

        $count = $messages;
        $emails = array();
        $this->log->debug("Retrieved $count new emails");
        for ($i = 1; $i <= $count; $i++) {
            // If result at $i - 1 > 50000, just delete, it's too big to process
            $size = $messageInfo[$i];
            
            if ($size > za()->getConfig('email_max_size', 100000)) {
                // skip for now, NEED TO SEND A BOUNCEBACK!
                $pop3->DeleteMessage($i);
                $this->log->err("Deleted email of size $size");
                continue;
            }
            
            if(($error=$pop3->RetrieveMessage($i,$headers,$body,-1))!="") {
                $this->log->err("Failed retrieving message: ".$error);
                continue;
            }

            $email = implode("\r\n", $headers);
            $email .= "\r\n\r\n". implode("\r\n", $body);
            $email .= "\r\n.";
            
            $decoder = new Mail_mimeDecode($email);
            $email = $decoder->decode(array('include_bodies'=>true));
            if ($delete) {
                // $this->popService->delete_mail($i);
	            if(($error=$pop3->DeleteMessage($i))!="") {
	                $this->log->err("Failed deleting message $i: ".$error);
	                continue;
	            }
	            $this->log->debug("Deleted message $i");
            } else {
                $this->log->debug("No messages being deleted");
            }

            if ($email !== false) {
				if ($this->isAutoReply($email)) {
					// log and quit
					$from = ifset($email->headers, 'from', "unknown@email");
					$this->log->warn("AutoReply email from ".$from." has been ignored");
				} else {
                	$emails[] = $email;
				}
            } else {
                $this->log->err("Failed decoding email $i");
            }
        }

        $pop3->Close();

        return $emails;
    }
    
    private function isAutoReply($email)
    {
    	$subject = ifset($email->headers, 'subject', '');
       	// check if the subject is actually an autoreply
		$toCheck = mb_strtolower($subject);
		return mb_strpos($toCheck, 'autoreply') !== false;
    }
    
    /**
     * Get the textual representation of the email body.
     *
     * @param array $email
     * @param string type The type of text to get (plain or html)
     * @return string
     */
    public function getTextBody($email, $type='plain')
    {
        if (!isset($email->ctype_primary) && !isset($email->ctype_secondary)) {
            // No primary content type specified
            return null;
        }

        if ($email->ctype_primary == 'text' && $email->ctype_secondary == $type && isset($email->body)) {
            return $email->body;
        } else if (isset($email->parts)) {
            foreach ($email->parts as $part) {
                $text = $this->getTextBody($part);
                if ($text != null) return $text;
            }
        }
    }
    
    /**
     * Get all the email attachments on an email 
     * 
     * Be wary as this returns the content of the file!
     *
     */
    public function getEmailAttachments($email)
    {
        $attachments = array();
        if (!isset($email->ctype_primary) && !isset($email->ctype_secondary)) {
            // No primary content type specified
            $this->log->warn("No content type specified in email, cannot read attachments");
            return $attachments;
        }
        
        $attachments = array();
        
        if (isset($email->disposition) && ($email->disposition == 'attachment' || $email->disposition == 'inline') && isset($email->body)) {
            $attachment = new RawFile();
            if (!isset($email->d_parameters['filename'])) {
                $email->d_parameters['filename'] = 'Email Attachment';
            }
            $attachment->filename = $email->d_parameters['filename'];
            $attachment->contentType = $email->ctype_primary.'/'.$email->ctype_secondary;
            if ($email->disposition == 'attachment') {
                $attachment->content = base64_decode($email->body);
            } else {
                $attachment->content = $email->body;
            }
            $attachments[] = $attachment;
        } else if (isset($email->parts)) {
            foreach ($email->parts as $part) {
                $attachPart = $this->getEmailAttachments($part);
                if (is_array($attachPart)) {
                    $attachments = array_merge($attachments, $attachPart);
                }
            }
        }

        return $attachments;
    }
	
	 /**
     * Enter description here...
     *
     * @param unknown_type $template
     * @param unknown_type $model
     */
    public function generateEmail($template, $model)
    {
        $view = new CompositeView();
        $view->setScriptPath(APP_DIR.'/views/emails');
        $view->assign($model);

        return $view->render($template);
    }
	
	/**
     * Notify the user (or users) about something
     *
     * @param string $subject
     * @param array|User $user
     * @param string $msg
     */
    public function sendEmail($subject, $users, $msg, $from=null, $fromname=null)
    {
        include_once 'Zend/Mail.php';
		
        if (is_string($users)) {
            $users = array($users);
        }
		
	    foreach ($users as $u) {
	        if (!($u instanceof NovemberUser)) {
	            $this->log->debug("Getting user for ".var_export($u, true));
	            $u = $this->userService->getUserByField('username', $u);
	        }
	        if ($u == null) {
	            $this->log->debug("Tried sending to non-existent user");
	            continue;
	        }

	        $mail = new Zend_Mail();
	        
	        if ($from == null) {
    		    $mail->setFrom(za()->getConfig('from_email'), za()->getConfig('name'));
    		} else {
    		    if (!$fromname) $fromname = za()->getConfig('name');
    		    $mail->setFrom($from, $fromname);
    		}
		    
	        $message = null;
	        if ($msg instanceof TemplatedMessage) {
	            $msg->model['user'] = $u;
	            $message = $this->generateEmail($msg->template, $msg->model);
	        } else {
	            $message = $msg;
	        }
	        $mail->setBodyText($message);

	        $mail->addTo($u->email, $u->username);
	        $mail->setSubject($subject);
	        
	        try {
	            $this->log->debug("Sending message '$subject' to ".$u->email);
                $mail->send();
    		} catch (Zend_Mail_Transport_Exception $e) {
    		    $this->log->debug(__CLASS__.':'.__LINE__." Failed sending mail: ".$e->getMessage());
    		}
	    }
    }
}

class RawFile
{
    public $contentType;
    public $filename;
    public $content;
}
?>