<?php

class CheckEmails implements RunnableTask 
{
    /**
     * @var EmailService
     */
    public $emailService;
    
    /**
     * @var IssueService
     */
    public $issueService;
    
    
    public function execute()
    {
        $server = za()->getConfig('support_mail_server');
        $user = za()->getConfig('support_email_user');
        $pass = za()->getConfig('support_email_pass');
        
        if (!$server || !$user || !$pass) {
            // exit!
            throw new Exception("Configuration incorrect for checking issue emails");
        }
        
		$emails = $this->emailService->readEmailFrom($server, $user, $pass, true);
		$this->issueService->processIncomingEmails($emails);
    }

    public function getTaskName()
    {
        return 'check-emails';
    }

    /**
     * The next run for this job will be the next saturday after the 
     * last run date
     */
    public function getNextRun($lastRun)
    {
        return 0;
    }
    
    /**
     * Runs every 5 minutes
     */
    public function getInterval()
    {
        return 5 * 60;
    }
}