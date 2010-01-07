<?php

class Codereview extends Bindable
{
    public $id;
    public $title;
    public $description;
    
    public $updated;
    public $created;
    
    public $status;
    
    /**
     * The revision number of the item in SVN
     *
     * @var unknown_type
     */
    public $revision;
    
    /**
     * A previous revision if any (can be empty)
     */
    public $previousrevision;
    
    /**
     * Who created the review? 
     *
     * @var unknown_type
     */
    public $author;

    public $projectid;
    
    public $diff;
    
    /**
     * @var array
     */
    public $difflog;
    
    /**
     * @var unmapped
     */
    public $cleardata = false;
    
    public $requiredFields = array('title', 'revision', 'projectid');
    
    /**
     * SvnService
     * 
     * This should actually be a generic interface to any SCM tool...
     *
     * @var unmapped
     */
    public $svnService;
    
    /**
     * ProjectService
     *
     * @var unmapped
     */
    public $projectService;
    
    /**
     * @var unmapped
     */
    public $notificationService;
    
    /**
     * @var unmapped
     */
    public $groupService;
    
    /**
     * @var unmapped
     */
    public $dbService;
    
    public $constraints = array();
    
    public function __construct()
    {
        $this->constraints['status'] = new CVLValidator(array('New', 'In Progress', 'Complete'));
    }

    /**
     * Get the list of diffs for this revision back to its previous revisions.
     *
     */
    public function getDiffList()
    {
    	$currentRevision = (int) $this->revision;
    	$lastRevision = (int) $this->previousrevision;
    	
    	if (!$currentRevision || !$currentRevision > 0) {
    		throw new Exception("Invalid revision number");
    	}

    	if ($lastRevision == null || !$lastRevision) {
        	$lastRevision = $currentRevision - 1;
    	}

    	$diff = null;
    	// see if we have some data for this revision already
        if ($this->diff == null || !mb_strlen($this->diff)) {
           	$project = $this->projectService->getProject($this->projectid);
	    	$this->diff = $this->svnService->getRawDiff($lastRevision, $currentRevision, $project->svnurl);
	    	$this->dbService->saveObject($this);
        } else {

        }

        $diff = $this->svnService->getDiffFromContent($this->diff);

    	return $diff;
    }

    /**
     * Get the stored log for this code review
     */
    public function getLog()
    {
        $currentRevision = (int) $this->revision;
    	$lastRevision = (int) $this->previousrevision;
    	
    	if (!$currentRevision || !$currentRevision > 0) {
    		throw new Exception("Invalid revision number");
    	}

    	if ($lastRevision == null || !$lastRevision) {
        	$lastRevision = $currentRevision - 1;
    	}
    	
        if ($this->difflog == null || !count($this->difflog)) {
            $project = $this->projectService->getProject($this->projectid);
            $logs = array();
            while ($currentRevision > $lastRevision) {
                $diffLog = $this->svnService->getRevisionLog($currentRevision, $project->svnurl);
                $logs = array_merge($logs, $diffLog);
                $currentRevision--;
            }
            $this->difflog = $logs;
            $this->dbService->saveObject($this);
        }
        return $this->difflog;
    }
    
    /**
     * When created, we need to notify the relevant review group
     * based on this project's group
     * 
     */
    public function created()
    {
        $this->author = za()->getUser()->getUsername();

        $project = $this->projectService->getProject($this->projectid);
        $group = $this->groupService->getGroup($project->ownerid);
        
        if ($group) {
            $users = $this->groupService->getUsersInGroup($group);
            
            // Foreach user, also assign them as watching for notifications
			foreach ($users as $user) {
				$this->notificationService->createWatch($user, $this);
			}

            $msg = new TemplatedMessage('new-codereview.php', array('model'=>$this, 'project'=>$project));
            try {
                $this->notificationService->notifyUser("New code review has been created", $users, $msg);
            } catch (Exception $e) {
                $this->log->debug("Failed sending notification: ".$e->getMessage());
            }
        }
    }
    
    /**
     * Called when this object is updated
     */
    public function update()
    {
        if ($this->cleardata) {
            $this->cleardata = 0;
            $this->difflog = array();
            $this->diff = '';
        }
    }
}
?>