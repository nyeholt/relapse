<?php

class Project extends MappedObject
{
    /**
     * Which project does this one belong to? 
     */
    public $parentid=0;
    
    /**
     * The group that is doing this project
     *
     * @var UserGroup
     */
    public $ownerid; // a group
    
    /**
     * The person managing this project.
     *
     * @var User
     */
    public $manager; // a user
    
    public $title;
    public $description;
    public $started; 
    public $actualstart;
    
    /**
     * The date it was paid
     */
    public $invoiceissued;
    
    public $due;
    public $completed;
    public $clientid;
    
    /**
     * An estimated number of days
     */
    public $estimated;

    /**
     * An estimate based on adding up the feature estimates
     */
    public $featureestimate;
    
    /**
     * An estimate based on adding up all the task estimates
     */
    public $taskestimate;
    
    /**
     * How much time has it actually taken so far? 
     */
    public $currenttime;
    
    public $rate = '100';
    
    /**
     * How many days was budgeted for this project? 
     */
    public $budgeted;

    public $deleted = 0;
    
    /**
     * The date when the Free Guarantee Period started
     */
    public $startfgp;
    
    /**
     * The duration in days for the FGP
     */
     public $durationfgp;

    /**
     * Do status reports get disabled automagically?
     */
    public $enablereports;

    /**
     * The url to this project's page
     *
     * @var string
     */
    public $url;
    
    /**
     * The url to the svn for this project
     */
    public $svnurl;

    /**
     * Can this project be viewed by external users? 
     * 
     * By default, this is FALSE
     *
     * @var int
     */
    public $isprivate = 1;
    
    /**
     * Is this project representative of a milestone?
     * 
     * @var int
     */
    public $ismilestone = 0;
    
    public $nextrelease = '';
    
    public $constraints = array();
    public $requiredFields = array('title');
    
    private $tasks;
    
    private $overview;

    public $searchableFields = array('title', 'deleted');
    
    /**
     * @var unmapped
     */
    public $projectService;
    
    /**
     * @var unmapped
     */
    public $featureService;
    
    /**
     * @var unmapped
     */
    public $clientService;
    
    /**
     * @var unmapped
     */
    public $groupService;

	/**
	 * @var unmapped
	 */
	public $versioningService;

    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
        $this->started = date('Y-m-d');
        $this->tasks = new ArrayObject();
        $this->manager = za()->getUser()->getUsername();
        $this->durationfgp = za()->getConfig('free_support_period', 90);
    }

		/**
	 *
	 * @return array
	 */
	public function listFields()
	{
		return array('id' => 'ID', 'title' => 'Title', 'currenttime' => 'Time Spent', 'budgeted' => 'Estimate', 'description' => 'Description');
	}

	/**
	 * Create a version when this project is created
	 */
	public function created()
	{
		if (!$this->ismilestone) {
			$this->createDefaultMilestone();
		}

		$this->versioningService->createVersion($this);
	}
    
    /**
     * Gets the hierarchy to this project
     */
    public function getHierarchy()
    {
    	$hierarchy = array();
    	if ($this->parentid) {
    		$parent = $this->projectService->getProject($this->parentid);
    		$hierarchy = $parent->getHierarchy();
    		$hierarchy[] = $parent;
    	} else {
    		$client = $this->clientService->getClient($this->clientid);
    		$hierarchy[] = $client;
    	}
    	
    	return $hierarchy;
    }

    /**
     * Is this project complete?
     */
    public function isComplete()
    {
    	return mb_strlen($this->completed);
    }

	/**
	 * Has this project started?
	 *
	 * @return boolean
	 */
    public function hasStarted()
    {
    	return mb_strlen($this->actualstart);
    }
    
    /**
	 * Get the name of the user who owns this project.
	 *
	 * @return string
	 */
	public function getUsername()
	{
	    return $this->manager;
	}
	
	public function setOverview($o)
	{
	    $this->overview = $o;
	}
	
	public function getOverview()
	{
	    if ($this->overview == null) {
	        $this->overview = $this->projectService->getProjectOverview($this);
	    }
	    return $this->overview;
	}
	
	/**
	 * Get this project's group
	 */
	public function getGroup()
	{
	    return $this->groupService->getGroup($this->ownerid);
	}
	
	/**
	 * Gets all the users assigned to this project's group
	 */
	public function getUsers()
	{
		$groupUsers = new ArrayObject();
		$group = $this->getGroup();
		if ($group != null) {
			$users = $this->groupService->getUsersInGroup($this->getGroup(), true);
			
	
	        foreach ($users as $user) {
	            $groupUsers[$user->id] = $user;
	        }	
		}
        
        return $groupUsers;
	}
	
	private $hasMilestones = null;
	
	/**
	 * Does this project have any milestones? 
	 */
	public function hasMilestones()
	{
		if ($this->hasMilestones === null) {
			$children = $this->getMilestones();
			$this->hasMilestones = count($children) > 0;
		}
		
		return $this->hasMilestones;
	}
	
	/**
	 * Gets all the tasks that are incomplete from this project
	 * 
	 * @return ArrayList
	 */
	public function getOpenTasks($user=null, $currentPage=null, $number=0)
	{
		$where = array(
			'projectid =' => $this->id,
		);

		return $this->getTasksWhere($where, 0, $currentPage, $number, $user);
	}

	/**
	 * Get all the tasks from the current project, complete or incomplete
	 */
	public function getAllTasks($user=null, $currentPage=null, $number=0)
	{
		$where = array(
			'projectid =' => $this->id,
		);

		return $this->getTasksWhere($where, null, $currentPage, $number, $user);
	}

	/**
	 * Count all tasks in this project
	 */
	public function countTasks($complete = null, $user=null)
	{
		$where = array(
			'projectid =' => $this->id,
		);
		if ($complete !== null) {
			$where['complete='] = $complete;
		}
		return $this->projectService->getTaskCount($where);
	}
	

	/**
	 * Count all incomplete tasks from this project AND its children
	 */
	public function countContainedTasks($complete = null, $user=null)
	{
		$ids = array($this->id);
		$ids = array_merge($ids, $this->getChildIds(true, 1));

		$where = array(
			'projectid' => $ids,
		);
		if ($complete !== null) {
			$where['complete='] = $complete;
		}

		return $this->projectService->getTaskCount($where); 
	}
	
	/**
	 * Gets all the tasks that are incomplete from this project AND its children
	 * 
	 * @return ArrayList
	 */
	public function getContainedOpenTasks($user=null, $currentPage=null, $number=0, $where = array())
	{
		$ids = array($this->id);
		$ids = array_merge($ids, $this->getChildIds(true, 1));
		
		$where = array(
			'projectid' => $ids,
		);

		return $this->getTasksWhere($where, 0, $currentPage, $number, $user);
	}
	
	/**
	 * Gets all the tasks that are from this project AND its children
	 * 
	 * @return ArrayList
	 */
	public function getContainedTasks($user=null, $currentPage=null, $number=0)
	{
		$ids = array($this->id);
		$ids = array_merge($ids, $this->getChildIds(true, 1));
		
		$where = array(
			'projectid' => $ids,
		);

		return $this->getTasksWhere($where, null, $currentPage, $number, $user);
	}
	
	
	/**
	 * Helper for getting tasks
	 */
	private function getTasksWhere($where = array(), $complete = null, $currentPage=null, $number=0, $user=null, $orderField = 'due', $orderDir='asc')
	{
		if ($complete !== null) {
			$where['complete='] = $complete;
		}
		
		if (!$number) {
			$number = za()->getConfig('project_task_list_size');
		}
		
		$order = $orderField . ' ' . $orderDir;
		
		if ($user !== null) {
			return $this->projectService->getUserTasks($user, $where, $order, $currentPage, $number);
		} else {
			return $this->projectService->getTasks($where, $order, $currentPage, $number);
		}
	}

	/**
	 * Get a list of all contained project child ids
	 * 
	 * @param $all whether to get all descendants or just immediate 
	 * @param $milestones whether to include the milestones or just projects
	 * 
	 * @return array
	 */
	public function getChildIds($all=false, $milestones = 0)
	{
		$childProjects = $this->getProjects($all, $milestones);
		// go through and build an 'in' clause from all child projects	
		
		$ids = array();
		foreach ($childProjects as $project) {
			$ids[] = $project->id;
		}
		return $ids;
	}
	
	/**
	 * @var unmapped
	 */
	public $milestones = null;
	
	/**
	 * Gets the milestones of this project, which are
	 * all the child projects with ismilestone set to true
	 * 
	 * @return ArrayObject
	 */
	public function getMilestones()
	{
		if ($this->milestones == null) {
			$this->milestones = $this->getProjects(false, 1);
		}

		return $this->milestones;
	}

	/**
	 * Create a default milestone for this project if none exists
	 */
	public function createDefaultMilestone($name="Project Completion") {
		return $this->projectService->createMilestone($this, $name, $this->due);
	}
	
	/**
	 * Get the features directly in this project
	 * 
	 * @return ArrayObject
	 */
	public function getFeatures()
	{
		return $this->featureService->getFeatures(array('milestone=' => $this->id));
	}
	
	/**
	 * @var unmapped
	 */
	public $containedMilestones = null;
	
	/**
	 * Get all contained (ie within this project and grandchild) milestones
	 */
	public function getContainedMilestones()
	{
		if ($this->containedMilestones == null) {
			$this->containedMilestones = $this->getProjects(true, 1);
		}

		return $this->containedMilestones;
	}
	
	/**
	 * @var unmapped
	 */
	public $subProjects = null;
	/**
	 * Get the direct sub projects of this project
	 */
	public function getSubProjects()
	{
		if ($this->subProjects == null) {
			$this->subProjects = $this->getProjects();
		}

		return $this->subProjects;
	}
	
	
	/**
	 * @var unmapped
	 */
	public $containedProjects = null;
	/**
	 * Get all contained projects
	 */
	public function getAllSubProjects()
	{
		if ($this->containedProjects == null) {
			$this->containedProjects = $this->getProjects(true);
		}

		return $this->containedProjects;
	}
	
	/**
	 * @var unmapped
	 */
	public $childProjects = null;

	/**
	 * Get all immediate child projects (milestones OR projects)
	 */
	public function getChildProjects()
	{
		if ($this->childProjects == null) {
			$this->childProjects = $this->getProjects(false, null);
		}
		
		return $this->childProjects;
	}
	
	/**
	 * Gets all the children of this project
	 */
	private function getProjects($grandChildren = false, $milestones = 0)
	{
		$where = array('parentid='=>$this->id);
		if ($milestones !== null) {
			$where['ismilestone='] = $milestones;
		}
		$children = $this->projectService->getProjects($where, 'due asc');
		if (!$grandChildren) {
			return $children;
		}

		// now get all of its children too
		$grandkids = new ArrayObject();
		foreach ($children as $childProject) {
			$grandkids[] = $childProject;
			// get its children and add them too
			$nextChildren = $childProject->getProjects(true, $milestones);
			foreach ($nextChildren as $nc) {
				$grandkids->append($nc);
			}
		}
		
		return $grandkids;
	}

	/**
	 * Returns a time value for the date the free support period ends 
	 */
	public function getFreeSupportEndDate() {
		if (!$this->startfgp) {
			return '';
		}
		$time = strtotime($this->startfgp." + ".$this->durationfgp." days");
		return $time;
	}
	
	/**
	 * Returns the number of days left of free support 
	 */
	public function getFreeSupportDays() {
		if (!$this->startfgp) {
			return 0;
		}
		$end = $this->getFreeSupportEndDate();
		$diff = $end - time();
		if ($diff < 0) {
			return 0;
		}
		
		return round($diff / 60 / 60 / 24);
	}
	
}

class ProjectVersion extends Project
{
	/**
	 * The original record's ID
	 *
	 * @var int
	 */
	public $recordid;
	public $versioncreated;
	public $validfrom;
	public $label;

	public function created() {}
	public function update() {}
}
?>