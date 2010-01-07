<?php

include_once 'extensions/expenses/model/Expense.php';

class ExpenseNotification implements RunnableTask 
{
    private $day = 'Sun';
    private $timeFormat = 'Y-m-d 11:30:00';
    
    /**
     * 
     * @var ExpenseService
     */
    public $expenseService;
    
    /**
     * NotificationService
     *
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var UserService
     */
    public $userService;
    
    public function execute()
    {
        $expenses = $this->expenseService->getExpenses(array('status=' => Expense::PENDING));
        
        $approvers = $this->userService->getApprovers();
        if (count($approvers)) {
            // Notify of the application
            $msg = new TemplatedMessage('pending-expenses.php', array('expenses'=>$expenses));
            $this->notificationService->notifyUser('Pending Expenses', $approvers, $msg);
        }
    }

    public function getTaskName()
    {
        return 'expense-notification';
    }

    /**
     * The next run for this job will be the next saturday after the 
     * last run date
     */
    public function getNextRun($lastRun)
    {
        $nextTime = $lastRun + 86400; 
        $now = time();
        // we want the next Saturday evening (ie once a week)
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