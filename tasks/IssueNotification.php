<?php
class IssueNotification implements RunnableTask 
{
private $day = 'Sun';
    private $timeFormat = 'Y-m-d 23:30:00';
    
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
        $issues = $this->issueService->getIssues(array('status=' => 'Open'));
        
        // Get the project for each issue
        $group = $this->groupService->getGroupByField('title', za()->getConfig('issue_group')); 
        if ($group) {
            $users = $this->groupService->getUsersInGroup($group);
            $msg = new TemplatedMessage('open-issues.php', array('issues'=>$issues));
            $this->notificationService->notifyUser('Open Requests', $users, $msg);
        } else {
            za()->log()->warn("Could not find group for sending issues to");
        }

    }

    public function getTaskName()
    {
        return 'issue-notification';
    }

    /**
     * The next run for this job will be the next sun after the 
     * last run date
     */
    public function getNextRun($lastRun)
    {
        $nextTime = $lastRun + 86400; 
        $now = time();
        // we want the next sun evening (ie once a week)
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