<?php

include_once 'model/Feature.php';
include_once 'model/Task.php';
class ProjectStatus extends Bindable
{
    public $id;
    public $title;
    
    public $projectid;
    
    public $updated;
    public $created;

    public $creator;
    
    /**
     * The project manager's subjective view on stuff that's 
     * been completed during the week
     * @var text
     */
    public $completednotes;
    
    /**
     * The subjective view on what's still left to complete for
     * next week
     */
    public $todonotes;

    /**
     * Which milestone is this report for?
     */
    public $milestone;
    
    public $startdate;
    
    public $enddate;
    
    public $dategenerated;
    
    /**
     * The serialised object representation of the status of the project at the time
     * it was generated
     *
     * @var object
     */
    public $snapshot;
    
    /**
     * @var unmapped
     */
    public $projectService;
    
    /**
     * When created, generate the status automatically. 
     */
    public function created()
    {
    	$this->generateStatus();
    }

    /**
     * Generate a representation of this project's current status. 
     */
    public function generateStatus()
    {
    	if (!$this->id || !$this->projectid) {
    		throw new Exception("Project status cannot be generated yet");
    	}
    	
    	$theProject = $this->projectService->getProject($this->projectid);
    	$theMilestone = $this->projectService->getProject($this->milestone);

    	$this->snapshot = new stdClass();
    	
    	// first get all the features and tasks
    	$this->snapshot->features = $theMilestone->getFeatures();
    	
    	foreach ($this->snapshot->features as $feature) {
    		$feature->getTasks();
    	}

    	$this->dategenerated = date('Y-m-d H:i:s');
    	// 
		$this->projectService->saveStatus($this);
    }
    
    public function getRecordedTime()
    {
    	$theProject = $this->projectService->getProject($this->projectid);
    	$records = $this->projectService->getTimesheetReport(null, $theProject, null, -1, $this->startdate, $this->enddate);
    	
    	$userTimeMapping = array();
    	foreach ($records as $record) {
    		$current = ifset($userTimeMapping, $record->user, 0);
    		// go through all times 
			foreach ($record->days as $daytime) {
				$current += $daytime;
			}
			$userTimeMapping[$record->user] = $current;
    	}
    	
    	return $userTimeMapping;
    }
}
?>