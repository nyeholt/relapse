<?php
class ProjectStatusCreation implements RunnableTask 
{
    private $day = 'Fri';
    private $timeFormat = 'Y-m-d 11:30:00';
    
    /**
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
    
    public function execute()
    {
	return;
        // go through each project that has reportgeneration = true
        $projects = $this->projectService->getProjects(array('enablereports='=>1));
        $userProjectMapping = new ArrayObject();
        foreach ($projects as $project) {
            /* @var $project Project */
            // Create the status report and notify the manager to go and check it out
            $report = $this->projectService->getProjectStatus($project);
            $report->title = "Report generated ".date('Y-m-d');
            $report->completednotes = "TO BE FILLED IN BY ".$project->manager;
            $report->todonotes = "TO BE FILLED IN BY ".$project->manager;
            
            $this->projectService->saveStatus($report);
            // email the manager!
            $userProjects = ifset($userProjectMapping, $project->manager, array());
            $userProjects[] = $project;
            $userProjectMapping[$project->manager] = $userProjects;
        }
        
        foreach ($userProjectMapping as $user => $projects) {
            $msg = new TemplatedMessage('status-report-created.php', array('projects'=>$projects));
            $this->notificationService->notifyUser('Project status reports reminder', $user, $msg);
        }
    }

    public function getTaskName()
    {
        return 'project-notification';
    }

    /**
     * The next run for this job will be the next saturday after the 
     * last run date
     */
    public function getNextRun($lastRun)
    {
        $nextTime = $lastRun + 86400; 
        $now = time();
        // we want the next 'this->day' evening (ie once a week)
        while (date('D', $nextTime) != $this->day) {
            $nextTime += 86400;
        }

        $date = date($this->timeFormat, $nextTime);
        return strtotime($date);
    }
    
    public function getInterval()
    {
        return 0;
    }
}
?>
