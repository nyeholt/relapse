<?php
class Feature extends MappedObject
{
    public $title;
    public $description;

	public $assumptions;
	public $questions;

    public $implementation;
    public $verification;
    
    /**
     * Estimate in days
     */
    public $estimated;
    public $hours;

    public $priority;
    
    /**
     * Which milestone should this feature be completed in? 
     *
     * @var int
     */
    public $milestone;
    
    /**
     * The title of the selected milestone, set by a query
     *
     * @var string
     */
    private $milestoneTitle = '';
    
    /**
     * The path to the feature. If root, 
     * this is a 'root' feature, whose title
     * makes up the overall document title.
     *
     * @var string
     */
    public $parentpath;

	/**
	 * Which project does the feature belong to
	 *
	 * @var int
	 */
    public $projectid;
    
    public $sortorder = 0;
    
	/**
	 * Has it been signed off ready to go?
	 */
	public $status;
    
    /**
     * @var unmapped
     */
    public $projectService;

	/**
	 *
	 * @var unmapped
	 */
	public $versioningService;

	/**
	 *
	 * @var unmapped
	 */
	public $dbService;
    
    /**
     * @var unmapped
     */
    public $itemLinkService;

    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
        $this->childFeatures = new ArrayObject();
		$this->constraints['status'] = new CVLValidator(array('Planning', 'Approved', 'Complete'));
    }

	/**
	 * Return an array of fields that will be used for displaying a JSON
	 * serialisation of this object
	 *
	 * @return array
	 */
	public function listFields()
	{
		return array('id' => 'ID', 'title' => 'Title', 'description' => 'Description', 'estimated' => 'Estimated', 'status' => 'Status', 'getPercentageComplete' => 'Percentage Complete');
	}

	/**
	 * Is this feature finished?
	 */
	public function isComplete()
	{
		return $this->status == 'Complete';
	}

	/**
	 * Whenever a feature is saved, it should create a new version of
	 * itself as well as the project it is associated with, regardless if 
	 * it's a new feature or an existing one
	 */
	public function created()
	{
		$parent = $this->projectService->getProject($this->projectid);
		if ($parent) {
			$this->versioningService->createVersion($parent, 'featureupdate');
		}
		$this->versioningService->createVersion($this);
	}

	/**
	 * When updated, make sure to see whether to create a new version
	 */
	public function update()
	{
		$parent = $this->projectService->getProject($this->projectid);
		$otherParent = null;
		// load the current state
		$current = $this->dbService->getById($this->id, 'Feature');
		if ($current->projectid != $this->projectid) {
			$otherParent = $this->projectService->getProject($current->projectid);
		}
		// $mostRecent = $this->versioningService->getMostRecentVersion($this);
		if ($current->estimated != $this->estimated || $otherParent || $current->milestone != $this->milestone) {
			if ($otherParent) {
				$this->versioningService->createVersion($otherParent, 'featureupdate');
			}

			if ($parent) {
				$this->versioningService->createVersion($parent, 'featureupdate');
			}

			$this->versioningService->createVersion($current);
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
	
	
    private $childFeatures;
    
	
    public function getChildFeatures()
    {
        return $this->childFeatures;
    }
    
    public function setChildFeatures($features)
    {
        $this->childFeatures = $features;
    }
    
    public function addChild(Feature $feature)
    {
        $this->childFeatures[] = $feature;
    }
    
    public function setMilestoneTitle($title)
    {
    	$this->milestoneTitle = $title;
    }
    
	public function getMilestoneTitle()
    {
    	return $this->milestoneTitle;
    }

    /**
     * @var unmapped
     */
    public $tasks;
    
    /**
     * Get all the tasks attached to this feature
     *
     * @return array
     */
    public function getTasks()
    {
    	if ($this->tasks == null) {
    		$this->tasks = $this->itemLinkService->getLinkedItemsOfType($this->me(), 'from', 'Task');
    	}
    	return $this->tasks;
    }
    
    public function getPercentageComplete()
    {
		$percentageComplete = 0;
 		if ($this->estimated != 0 && $this->hours != 0) {
			$percentageComplete = ceil($this->hours / ($this->estimated * za()->getConfig('day_length', 8)) * 100);
 		}
 		return $percentageComplete;
    }
}

/**
 * Class for mapping versions of the feature
 */
class FeatureVersion extends Feature
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

	public function me()
	{
		$dbService = za()->getService('DbService');
		$type = substr(get_class($this), 0, strrpos(get_class($this), 'Version'));
		return $dbService->getById($this->recordid, $type);
	}

	public function saved() {}
	public function created() {}
	public function update() {}
}
?>