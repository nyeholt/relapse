<?php

class Issue extends MappedObject
{
    const CLOSED_STATUS = 'Closed';
    const STATUS_CLOSED = 'Closed';
    const STATUS_RESOLVED = 'Resolved';
    const STATUS_NEW = "New";
    
    const SEVERITY_ONE = "Severity 1";
    
    public $title;
    
    /** Reproduction steps */
    public $description;
    
    /**
     * The project related with this issue
     *
     * @var foreign key
     */
    public $projectid;
    
    /**
     * An issue can be associated with either a client or
     * a project, so allow for that here. 
     *
     * @var unknown_type
     */
    public $clientid;
    
    public $severity = 'Severity 3';
    public $issuetype;
    public $status = 'New';
    
    public $category;

    public $product = 'N/A';
    public $operatingsystem = 'N/A';
    public $databasetype = 'N/A';
    
    /**
     * Should this be viewable by non-staff members? 
     *
     * @var unknown_type
     */
    public $isprivate = 0;

    public $release;

    public $userid;

    public $estimated = 0;
	
    public $elapsed = 0;

    public $constraints = array();
    
    public $searchableFields = array('title', 'description', 'status', 'severity', 'issuetype', 'product', 'operatingsystem', 'databasetype');
    
    /**
     * @var unmapped
     */
    public $itemLinkService;
    
    /**
     * @var unmapped
     */
    public $notificationService;
    
    /**
     * @var unmapped
     */
    public $issueService;
    
    /**
     * @var unmapped
     */
    public $projectService;
    
    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
        $this->constraints['severity'] = new CVLValidator(array(self::SEVERITY_ONE, 'Severity 2', 'Severity 3'));
        $this->constraints['issuetype'] = new CVLValidator(array('TBA', 'Bug', 'Change', 'Support', 'Enhancement', 'Monthly Release', 'New Project'));
        $this->constraints['status'] = new CVLValidator(array('New', 'Open', 'In Progress', 'On Hold', 'Pending', 'Resolved', Issue::CLOSED_STATUS));
        
        $this->constraints['product'] = new CVLValidator(array('Alfresco', 'Documentum', 'Other', 'N/A'));
        $this->constraints['operatingsystem'] = new CVLValidator(array('Windows', 'Mac OSX', 'Linux', 'BSD', 'Solaris', 'Unix', 'Other', 'N/A'));
        $this->constraints['databasetype'] = new CVLValidator(array('MySQL', 'Oracle', 'PostgreSQL', 'Ingres', 'DB2', 'MSSQL', 'Other', 'N/A'));
    }
    
    /**
     * This will return a list of statuses
     * which make sense for an external user
     * to choose from according to the current
     * status of the issue.
     */
    public function getUserStatuses() {
    	if ($this->status == Issue::CLOSED_STATUS) {
    		return array(Issue::CLOSED_STATUS, "Open");
    	} else if ($this->status == "Pending") {
    		return array( "Pending", "Open", "Resolved");
    	} else if ($this->status == "Resolved") {
    		return array("Resolved", "Open");
    	} else {
    		return array($this->status);
    	}
    }
    
    /**
	 * Gets the hierarchy of this task
	 * 
	 * @return array()
	 */
	public function getHierarchy()
	{
		$hierarchy = array();
		if ($this->projectid) {
			$parent = $this->projectService->getProject($this->projectid);
			$hierarchy = $parent->getHierarchy();
			$hierarchy[] = $parent;
		}
		
		return $hierarchy;
	}
    
	
    /**
     * Get tasks that have been spawned from this issue
     */
    public function getTasks()
    {
    	return $this->itemLinkService->getLinkedItems($this->me());
    }
    
    /**
     * Get the notes for this issue
     *
     * @return ArrayList
     */
    public function getNotes()
    {
    	return $this->notificationService->getNotesFor($this->me());
    }
    
    /**
     * Get all the issues that make up the history of this issue
     *
     * @return ArrayList
     */
    public function getHistory()
    {
    	return $this->issueService->getIssueHistory($this->me());
    }
    
    /**
     * Go through the history and get the status of the issue at the given timestamp
     *
     * @param int $timestamp
     */
    public function getStatusAt($timestamp)
    {
    	if ($timestamp == null) return $this->status;
    	$history = $this->getHistory();
    	$oldest = null;
    	foreach ($history as $issue) {
    		// Get the lastchanged date. If the lastchanged is GREATER THAN the timestamp,
			// but less than now, then this is the status we want
			$date = strtotime($issue->lastchanged);
			if ($date >= $timestamp && $date <= time()) {
				$oldest = $issue->status;
			}
    	}
    	if ($oldest == null) $oldest = $this->status;
    	return $oldest;
    }
}

class IssueVersion extends Issue
{
	public $recordid;
	public $versioncreated;
	public $validfrom;
	public $label;

	public function me()
	{
		$dbService = za()->getService('DbService');
		$type = substr(get_class($this), 0, strrpos(get_class($this), 'Version'));
		return $dbService->getById($this->recordid, $type);
	}
}
?>