<?php

include_once 'model/Project.php';
class ProjectTimeUpdate implements RunnableTask 
{
	/**
	 * ProjectService
	 *
	 * @var ProjectService
	 */
    public $projectService;
    /**
     * @var DbService
     */
    public $dbService;
    private $lastRun;
    
    public function execute()
    {
    	$updateTime = (date('Y-m-d H:i:s', $this->lastRun));
    	$select = $this->dbService->select();
    	
    	$select->from('project')->joinInner('task', 'task.projectid = project.id', new Zend_Db_Expr('task.id as taskid'))
    		->where("task.updated > ?", $updateTime);

    	$projects = $this->dbService->fetchObjects('Project', $select);

    	$done = array();
    	
    	foreach ($projects as $project) {
    		if (in_array($project->id, $done)) {
    			continue;
    		}
    		$done[] = $project->id;
    		echo "Updating time for project #$project->id ".$project->title."\n";
    		$this->projectService->updateProjectEstimate($project);
    	}
    	
    	$select = $this->dbService->select();
    	$select->from('project')->joinInner('feature', 'feature.projectid = project.id', new Zend_Db_Expr('feature.id as featureid'))
    		->where("feature.updated > ?", $updateTime);

    	$projects = $this->dbService->fetchObjects('Project', $select);

    	foreach ($projects as $project) {
    		if (in_array($project->id, $done)) {
    			continue;
    		}
    		$done[] = $project->id;
    		echo "Updating time for project #$project->id ".$project->title."\n";
    		$this->projectService->updateProjectEstimate($project);
    	}
    	
    }

    public function getTaskName()
    {
        return 'project-time-update';
    }

    /**
     * The next run for this job is lastRun + 600, but we want $lastRun so we can store it locally
     */
    public function getNextRun($lastRun)
    {
    	$this->lastRun = $lastRun;
        return $lastRun + 600;
    }

    public function getInterval()
    {
        return 0;
    }
}

?>