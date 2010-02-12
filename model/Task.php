<?php

/**
 * Representation of a Task
 *
 * Changes:
 * * ALTER TABLE `task` ADD `dependency` VARCHAR( 255 ) NOT NULL AFTER `projectid` ;
 */
class Task extends MappedObject
{
	public $description;
	public $projectid;
	public $title;

	/**
	 * @var array
	 */
	public $userid;

	public $status;
	public $startdate;
	public $due;
	public $complete=0;
	public $timespent=0;
	public $estimated=1;

	/**
	 * Which task does this task depend on?
	 */
	public $dependency;

	/**
	 * What kind of task is it? Billable, Research, Idle etc
	 *
	 * @var string
	 */
	public $category = '';

	/** This field is pulled down in some selects only */
	private $projecttitle;
	private $clienttitle;

	public $constraints = array();
	public $requiredFields = array('title');
	public $searchableFields = array('title', 'description', 'status');

	/**
	 * @var unmapped
	 */
	public $itemLinkService;

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
		$this->constraints['category'] = new CVLValidator(array('Billable', 'Unbillable', 'Support', 'Free Support'));
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
	
	public function getMilestone()
	{
		return $this->projectService->getProject($this->projectid);
	}
	
	/**
	 * The project is the parent project of the milestone. 
	 */
	public function getProject()
	{
		$milestone = $this->getMilestone();
		if ($milestone->ismilestone) {
			return $this->projectService->getProject($milestone->parentid);
		}
		return $milestone;
	}

	/**
	 * Get the percentage complete of this task
	 */
	public function getPercentage()
	{
		if ($this->timespent && $this->estimated) {
			return ($this->timespent / ($this->estimated * 3600)) * 100;
		}

		return '0.0';
	}

	private $percentageColours = array('#FF4B4B', '#FF875B', '#FFAB68', '#FFBF7C', '#FFF28E', '#DBFF8C', '#A5FF76', '#67FF53', '#3DFF4F', '#00FF40');

	/**
	 * get the percentage complete as a colour
	 *
	 *
	 *
	 */
	public function getPercentageColor()
	{
		$complete = $this->getPercentage();
		if ($complete == '0.0') {
			return $this->percentageColours[0];
		}
		$complete = $complete / 10;
		$complete = floor($complete);
		if ($complete > 9) $complete = 9;

		return $this->percentageColours[$complete];
	}

	private $ageColours = array('#FF4B4B', '#FF5396', '#FF6FCC', '#FF7CFD', '#D076FF', '#815BFF', '#4135FF');
	/**
	 * Get how "stale" this task is in terms of how long
	 * it has been since it has been edited
	 *
	 * @return String
	 */
	public function getStalenessColor()
	{
		$now = time();
		$lastmod = strtotime($this->updated);

		$diff = $now - $lastmod;

		if ($diff == 0) {
			return $this->ageColours[0];
		}

		$day = 86400;
		$age = $diff / $day;
		$age = floor($age);
		if ($age > 6) $age = 6;

		return $this->ageColours[$age];
	}
	
	/**
	 * Assigns this task to a particular user(s)
	 * 
	 * @param mixed $user The user or array of users to assign the task to
	 */
	public function assignTo($user)
	{
		if (!is_array($user)) {
			$user = array($user);
		}
		$this->userid = $user;

		$this->projectService->saveTask($this);
	}

	/**
	 * Get the duration of this timesheet entry as a
	 * formatted string
	 *
	 * @param string $format The format is any valid date() format. It is
	 * rooted at 0, so only the hour / minutes really are applicable.
	 */
	public function getDuration($format="H:i:s")
	{
		$diff = $this->timespent;

		$days = gmdate("d", $diff) - 1;
		$hours = gmdate("H", $diff);
		$hours = $hours + $days * 24;
		$mins = gmdate("i", $diff);
		return $hours.":".$mins;
	}

	public function setProjectTitle($t)
	{
		$this->projecttitle = $t;
	}

	public function setClientTitle($t)
	{
		$this->clienttitle = $t;
	}

	public function getProjectTitle()
	{
		return $this->projecttitle;
	}

	public function getClientTitle()
	{
		return $this->clienttitle;
	}

	/**
	 * Gets the ID to use for dependants of this task
	 */
	public function getDependencyId()
	{
		$dependency = (mb_strlen($this->dependency) ? '' : '-').$this->dependency.$this->id.'-';
		return $dependency;
	}

	/**
	 * Called when the task is 'started'
	 *
	 */
	public function start()
	{
		$issues = $this->itemLinkService->getLinkedItems($this, 'to', 'Issue');

		foreach ($issues as $issue) {
			if ($issue->status == 'New' || $issue->status == 'Open') {
				$this->log->debug("Updating status for request #{$issue->id}");
				$issue->status = 'In Progress';
				$this->issueService->saveIssue($issue);
			}
		}
	}
}
?>