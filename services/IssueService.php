<?php

include_once 'model/Issue.php';

class IssueService
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;

    /**
     * ClientService
     *
     * @var ClientService
     */
    public $clientService;

    /**
     * UserService
     *
     * @var UserService
     */
    public $userService;

    /**
     * ProjectService
     *
     * @var ProjectService
     */
    public $projectService;

    /**
     * NotificationService
     *
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var SmsService
     */
    public $smsService;
    
    /**
     * The email service
     *
     * @var EmailService
     */
    public $emailService;
    
    /**
     * @var FileService
     */
    public $fileService;
    
    /**
     * The ItemLinkService
     *
     * @var ItemLinkService
     */
    public $itemLinkService;

    public $groupService;
    
    /**
     * Tracker Service
     *
     * @var TrackerService
     */
    public $trackerService;

	/**
	 *
	 * @var VersioningService
	 */
	public $versioningService;
    
    public function getIssue($id)
    {
        return $this->dbService->getById($id, 'Issue');
    }

    /**
     * Get all the issues for a given project
     *
     * @param Project $project
     * @param unknown_type $where
     * @return ArrayObject
     */
    public function getProjectIssues(Project $project, $where=array())
    {
        $where['projectid='] = $project->id;
        return $this->getIssues($where);
    }

    /**
     * Get a list of issues.
     *
     * @return arrayObject
     */
    public function getIssues($where=array(), $order='issue.created desc', $page=null, $number=null)
    {
        // if the current user is an external, filter by their clientid
        if (za()->getUser()->getRole() == User::ROLE_EXTERNAL) {
            // get their client
            $client = $this->clientService->getUserClient(za()->getUser());
            $where['issue.clientid='] = $client->id;
        }
        
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
        $select->from('issue', '*');

        $select->joinInner('client', 'client.id=issue.clientid', 'client.title as clientname');
		$select->joinLeft('project', 'project.id=issue.projectid', array('project.title as projectname', 'project.isprivate as privateproject'));
        
        $this->dbService->applyWhereToSelect($where, $select);
        
        if (!is_null($page)) {
            $select->limitPage($page, $number);
        }

        $select->order($order);

        $tasks = $this->dbService->fetchObjects('Issue', $select);

        return $tasks;
    }

    /**
     * Get the total number of clients for a given where clause
     *
     * @param array $where
     * @return int
     */
    public function getIssueCount($where)
    {
        // if the current user is an external, filter by their clientid
        if (za()->getUser()->getRole() == User::ROLE_EXTERNAL) {
            // get their client
            $client = $this->clientService->getUserClient(za()->getUser());
            $where['clientid='] = $client->id;
        }
        return $this->dbService->getObjectCount($where, 'Issue');
    }
    
    /**
     * Gets the history for an issue
     * 
     * @param Issue $issue the issue to get the history for
     */
    public function getIssueHistory($issue)
    {
    	if ($issue == null) return array();

		$items = $this->versioningService->getVersionsFor($issue);
        
        return $items;
    }
    
    /**
     * Gets all the categories a client has set up for their issues
     */
    public function getIssueCategoriesForCompany(Client $client)
    {
        $query = 'select distinct(`category`) from issue where clientid=?';
        $result = $this->dbService->query($query, array($client->id));
        /* @var $result Zend_Db_Statement_Pdo */
        $res = $result->fetchAll(PDO::FETCH_COLUMN);
        return $res;
    }
    
    /**
     * Get all the releases for a given project
     *
     * @param Project $project
     */
    public function getProjectReleases(Project $project)
    {
        $query = 'select distinct(`release`) from issue where projectid=?';
        $result = $this->dbService->query($query, array($project->id));
        /* @var $result Zend_Db_Statement_Pdo */
        $res = $result->fetchAll(PDO::FETCH_COLUMN);
        return $res;
    }

    /**
     * Saves an issue to the database
     *
     * @param array|Issue $params
     */
    public function saveIssue($params, $doNotify = true)
    {
        $issue = null;
        $sendNotification = false;

        if (is_array($params)) {
            $existingId = ifset($params, 'id');
        } else {
            $existingId = $params->id;
        }

        if (!$existingId) {
            $sendNotification = true;
        }
        
        $oldIssue = null;
        // If there's an existing one, save it in the history
        if ($existingId) {
            $oldIssue = $this->getIssue($existingId);
            $this->versioningService->createVersion($oldIssue);
        }

        // If saving a new one, we want to
        $issue = $this->dbService->saveObject($params, 'Issue');

        // Okay, now check if it's on a project or not
		$project = null;
        if (!$issue->projectid) {
            // Get the project
            $client = $this->clientService->getClient($issue->clientid);
            if (!$client) {
                throw new Exception("No client exists with ID $issue->clientid");
            }
            $project = $this->clientService->getClientSupportProject($client);
            if (!$project) {
                throw new Exception("Missing client details for request $issue->title");
            }
            $issue->projectid = $project->id;
            $this->dbService->updateObject($issue);
        } else {
        	$project = $this->projectService->getProject($issue->projectid);
        }
        
        // make sure it's assigned to someone
        if (!mb_strlen($issue->userid)) {
	        if (!$project) {
	        	$project = $this->projectService->getProject($issue->projectid);
	        } 
	        $issue->userid = $project->manager;
	        $this->dbService->updateObject($issue); 
        }
        
        // Check to see if the assignee has a watch, if not, add one
        $assignedTo = $this->userService->getByName($issue->userid);
        if ($assignedTo) {
            $existing = $this->notificationService->getWatch($assignedTo, $issue->id, 'Issue');
            if (!$existing) {
                $this->notificationService->createWatch($assignedTo, $issue->id, 'Issue'); 
            }
        }

        $this->log->debug("Saving request, notify $issue->userid = ".$sendNotification);
        $this->trackerService->track('create-issue', $issue->id);

        // now send a notification to those users in the
        // group assigned to the project the issue was just saved against.
        if ($sendNotification && $doNotify) {
        	
        	// create and assign a task to whoever was assigned to the issue 
			// by default
			if ($assignedTo && false) {
				// Create the task
				$task = new Task();
				$task->projectid = $issue->projectid;
				$task->userid = array($assignedTo->username);
				$task->title = 'Respond to request "'.$issue->title.'"';
				$task->description = "Please investigate this request:\r\n\r\n".$issue->description;
				$task->category = 'Support';
				$task->due = date('Y-m-d H:i:s', strtotime('+2 days')); 
				// $task = $this->projectService->saveTask($task, false, true);
				// $this->itemLinkService->linkItems($issue, $task);
			}

            $this->log->debug("Notifying users about new request $issue->title");
            $this->notifyOfNewIssue($issue);
            
            // Add a watch for the current user. Note that it might be null if
            // this issue is created from an email
            $user = za()->getUser();
            if ($user) {
                $this->notificationService->createWatch($user, $issue->id, 'Issue');
            }
        }
        
        if ($issue->status == Issue::STATUS_CLOSED) {
        	// remove notifications
			$subscribers = $this->notificationService->getSubscribers($issue);
			foreach ($subscribers as $username => $item) {
				$user = $this->userService->getUserByField('username', $username);
				if ($user) {
					$this->notificationService->removeWatch($user, $issue->id, 'Issue');
				}
			}
        }

        // See if the status has changed and notify the creator
        if ($oldIssue != null && $oldIssue->status != $issue->status && $doNotify) {
            try {
                $this->notifyOfUpdatedIssue($oldIssue, $issue);
            } catch (Exception $e) {
                $this->log->warn("Failed to notify of request update"); 
            }
        }

        return $issue;
    }

    /**
     * Notify the relevant people of a new issue
     *
     * @param unknown_type $issue
     */
    private function notifyOfNewIssue($issue)
    {
        // get the group assigned to the project that this issue is against
        $project = $this->projectService->getProject($issue->projectid);
        $group = $this->groupService->getGroup($project->ownerid);

        // Only send if the group exists
        if ($group) {
            $users = $this->groupService->getUsersInGroup($group);
            $msg = new TemplatedMessage('new-issue.php', array('model'=>$issue, 'project'=>$project));
            
            $this->notificationService->notifyUser("New request has been created", $users, $msg);
        }
        
        // if the new issue is a severity 1, then sms as well
		if ($issue->severity == Issue::SEVERITY_ONE) {
			$this->trackerService->track('sev1request', 'request-'.$issue->id);
			$user = $this->userService->getUserByField('username', $issue->userid);
			if ($user != null && $user->contactid) {
				$contactDetails = $this->clientService->getContact($user->contactid);
				if ($contactDetails && mb_strlen($contactDetails->mobile)) {
					// lets send a quick SMS
					if ($this->smsService->send("New Severity 1 request #".$issue->id.": ".$issue->title.", please login and check ASAP!", $contactDetails->mobile)) {
						$this->trackerService->track('sendsms', 'request-'.$issue->id);
					}
				}
			}
		}
    }
    
    /**
     * Notify of an issue status change
     */
    private function notifyOfUpdatedIssue(Issue $oldIssue, Issue $issue)
    {
        $oldStatus = $oldIssue->status;
        
        $this->log->debug("Notifying ".$issue->creator." of status update");
        $this->trackerService->track('issue-status-change', 'Issue-'.$issue->id, null, null, "Notifying ".$issue->creator." of status update from $oldStatus to ".$issue->status);
        $msg = new TemplatedMessage('issue-status-changed.php', array('model'=>$issue, 'oldStatus'=>$oldStatus));
        $this->notificationService->notifyUser("Request updated", $issue->creator, $msg);
    }

    /**
     * Processes incoming emails so that issues can be created/updated
     *
     * @param unknown_type $emails
     */
    public function processIncomingEmails($emails)
    {
        foreach ($emails as $mail) {
            // First we need to find which customer the email belongs to
            $from = ifset($mail->headers, 'from', false);
            if (!$from) {
                za()->log("Failed finding from email header in ".print_r($mail, true));
                continue;
            }

            // clean it up a bit
            if (!preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i", $from, $matches)) {
                za()->log("Error finding valid email address", Zend_Log::ERR);
                continue;
            }

            $email = $matches[0];

            // Get the contact now. If it doesn't exist that's okay, it
            // might be a system user instead.
            $contact = $this->clientService->getContactByField('email', $email);

            // If not found by primary, try secondary
            if (!$contact) {
                $contact = $this->clientService->getContactByField('altemail', $email);
            }

            // We'll also see whether this issue was sent in by a user
            // of the system.
            $user = $this->userService->getUserByField('email', $email);

            if (!$contact && !$user) {
                za()->log("No valid user found with 'from' address $email", Zend_Log::WARN);
                $this->trackerService->track('invalid-issue-email', $email, null, null, serialize($mail));
                continue;
            }

            // Next up, see if the contact has an associated user, because
            // we'll add them in as the 'creator' of the issue
            if ($contact != null) {
                $contactUser = $this->userService->getUserbyField('contactid', $contact->id);
                if ($contactUser != null) {
                    $user = $contactUser;
                }
            }
            
            if ($user != null) {
                za()->setUser($user);
            }

            $params = array();

            // Saving a new issue uses the title of the email
            $subject = ifset($mail->headers, 'subject', false);
            if (!$subject) {
                // we'll accept an empty subject, just create a default title
                $subject = "Support request from $from";
            }
            

            $textBody = $this->emailService->getTextBody($mail);
            $issue = null;
            // Try and get an existing ticket
            za()->log("Checking email subject $subject");
            if (preg_match("/#(\d+)/", $subject, $matches)) {
                // existing
                $id = $matches[1];
                $issue = $this->getIssue($id);

                if ($issue) {
	                za()->log("Adding note to request $id");
	
	                // Make sure the issue found currently belongs to the contact
	                // client!
	                // if there's no contact, make sure there's a user instead
	                if ($contact) {
	                    if ($issue->clientid != $contact->clientid) {
	                        $issue = null;
	                    }
	                } else if (!$user) {
	                    $issue = null;
	                }
                } else {
                	$this->log->warn("Request not found for id $id");
                }
            }
            
            $infoText = "";
            if ($user) {
                $infoText = "Email coming from user #".$user->id." -  ".$user->username." ";
            } 
            
            if ($contact) {
                $infoText = "Email coming from contact #".$contact->id." - ".$contact->firstname." "; 
            }

            $this->trackerService->track('incoming-issue-email', $email, null, null, "Processing email from ".$email.": ".$infoText); 
            $notifyOfId = false;
            
            // If we've already got an issue, it means we're wanting to
            // just update its comments
            if ($issue) {
                // Just add an additional comment to the
                // current issue.
                $poster = $contact ? $contact->firstname : $user->getUsername();
                $note = $this->notificationService->addNoteTo($issue, $textBody, $subject, $poster);
                $this->notificationService->sendWatchNotifications($note, array('controller' => 'issue', 'action' => 'edit', 'params'=>array('id'=>$issue->id)));

                za()->log("Note added to request $issue->id", Zend_Log::INFO);
            } else {
                // new
                $issue = new Issue();
                $issue->title = $subject;
                $issue->description = $textBody;
                $issue->issuetype = 'Support';

                // Send a notification to the user that the report was received,
                // with the bug ID in it so the user knows what to respond to
                $notifyOfId = !is_null($contact);
            }
            
            
            if ($contact) {
                $issue->clientid = $contact->clientid;
            }

            $this->saveIssue($issue); 

            // Go through the attachments for the email, if any
            $attachments = $this->emailService->getEmailAttachments($mail);
            
            $i = 1;
            foreach ($attachments as $emailAttachment) {
                $this->addAttachmentToIssue($issue, $emailAttachment, $i);
                $i++;
            } 

            if ($notifyOfId) {
                $model = array(
	                'issue' => $issue,
	                'contact' => $contact,
                );

                $receipient = new User();
                $receipient->username = $contact->firstname;
                $receipient->email = $contact->email;

                $subject = "New request created: #$issue->id";
                $msg = $this->notificationService->generateEmail('new-issue-contact.php', $model);
                $this->trackerService->track('notify-request-sender', $receipient->username, null, null, $subject);
                $this->notificationService->notifyUser($subject, array($receipient), $msg, ifset($mail->headers, 'to', null), 'Support');
            }
        }
    }
    
    /**
     * Adds a file attachment to an issue. 
     */
    private function addAttachmentToIssue(Issue $issue, RawFile $file, $attnum=0)
    {
        // First we need to make sure we've got the filepath for the
        // client this issue is attached to. 
        $client = $this->clientService->getClient($issue->clientid);
        
        if (!$client) {
            throw new Exception("Invalid request for attaching files to");
        }
        $path = 'Clients/'.$client->title.'/Issues/'.$issue->id;
        if (!$this->fileService->createDirectory($path)) {
            throw new Exception("Failed creating directory $path for saving request files");
        }
        
        $existing = $this->fileService->getFileByPath($path.'/'.$file->filename);
        if ($attnum && $file->filename == 'Email Attachment') {
            $file->filename .= " #$attnum";
        }

        if (!$existing) {
            $existing = $this->fileService->createFile($file->filename, $path);
        }

        $this->fileService->setFileContent($existing, $file->content, $file->contentType);
    }
    
    /**
     * Gets the list of attachments for a given issue
     *
     * @param unknown_type $issueId
     */
    public function getIssueFiles(Issue $issue)
    {
        $client = $this->clientService->getClient($issue->clientid);
        
        if (!$client) {
            throw new Exception("Invalid request for attaching files to");
        }

        $path = 'Clients/'.$client->title.'/Issues/'.$issue->id;

        $fileService = za()->getService('FileService');
        
        $files = array();
        try {
            $files = $fileService->listDirectory($path);
        } catch (Exception $e) {
            $this->log->err("Failed retrieving files from $path; ".$e->getMessage());
            $files = array();
        }
         
        return $files;
    }
}

?>