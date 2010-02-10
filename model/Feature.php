<?php
class Feature extends Bindable 
{
    public $id;
    public $created;
    public $updated;
	public $creator;
	public $modifier;
    
    public $title;
    public $description;
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
     * Is this complete?
     *
     * @var int
     */
    public $complete = 0;
    
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
		// load the current state
		$current = $this->dbService->getById($this->id, 'Feature');
		// $mostRecent = $this->versioningService->getMostRecentVersion($this);
		if ($current->estimated != $this->estimated) {
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
    		$this->tasks = $this->itemLinkService->getLinkedItemsOfType($this, 'from', 'Task');
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

	public $validfrom;
	public $label;

    public function saved()
	{
		// don't need to do anything special for a feature's version
	}

	/**
     * Get all the tasks attached to this feature
     *
     * @return array
     */
    public function getTasks()
    {
    	if ($this->tasks == null) {
    		$this->tasks = $this->itemLinkService->getLinkedItemsOfType($this, 'from', 'Task');
    	}
    	return $this->tasks;
    }

	public function created() {}
	public function update() {}
}
?>