<?php

class IssueCleanup implements RunnableTask 
{
	/**
     * 
     * @var IssueService
     */
    public $issueService;
    
    /**
     * NotificationService
     *
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var GroupService
     */
    public $groupService;
    
    public function execute()
    {
    	$lastMonth = date('Y-m-d', strtotime('-1 month')).' 00:00:00';
        $issues = $this->issueService->getIssues(array('status=' => Issue::STATUS_RESOLVED, 'issue.updated < '=>$lastMonth));

        foreach ($issues as $issue) {
        	$issue->status = Issue::STATUS_CLOSED;
        	$this->issueService->saveIssue($issue, false);
        	echo "Closed request ".$issue->id.": ".$issue->title."\r\n";
        }
    }

    public function getTaskName()
    {
        return 'issue-notification';
    }

    /**
     * The next run for this job will be the next saturday after the 
     * last run date
     */
    public function getNextRun($lastRun)
    {
        
    }
    
    public function getInterval()
    {
        return 86400;
    }
}
?>