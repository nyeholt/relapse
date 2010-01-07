<?php
class ExpenseService
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * @var FileService
     */
    public $fileService;
    
    /**
     * ClientService
     * 
     * @var ClientService
     */
    public $clientService;
    
    /**
     * TrackerService
     * @var TrackerService
     */
    public $trackerService;
    
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var UserService
     */
    public $userService;

    public function getExpense($id)
    {
        
        return $this->dbService->getById((int) $id, 'Expense');
    }
    
    /**
     * Save an expense
     */
    public function saveExpense($expense)
    {
        $newExpense = false;
        
        if (is_array($expense)) {
            if (!isset($expense['id'])) {
                $newExpense = true;
            }
            $expense = $this->dbService->saveObject($expense, 'Expense');
        } else {
            $expense = $this->dbService->saveObject($expense);
        }
        
        return $expense;
    }
    
    /**
     * Save an expense Report
     * 
    * @param ExpenseReport $report 		the report to save
     */
    public function saveReport(ExpenseReport $report)
    {
        return $this->dbService->saveObject($report);
    }

    /**
     * Gets the list of locations expenses are located
     * in. 
     */
    public function getExpenseLocations()
    {
        $query = 'select distinct(location) from expense';
        $result = $this->dbService->query($query);
        /* @var $result Zend_Db_Statement_Pdo */
        $res = $result->fetchAll(PDO::FETCH_COLUMN);
        return $res;
    }

    /**
     * Get the expenses for a client
     * @param array $where 
     */
    public function getExpenses($where=array(), $page=null, $number=null)
    {
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from(strtolower('Expense'), '*');
		
		$select->joinLeft('project', 'project.id=expense.projectid', 'project.title as projecttitle');
		$select->joinLeft('client', 'client.id=expense.clientid', 'client.title as clienttitle');
        $select->joinInner('crmuser', 'crmuser.username=expense.username', array('crmuser.firstname as firstname', 'crmuser.lastname as lastname'));
        foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}

		$select->order('expensedate desc');
		
        if (!is_null($page)) {
            $select->limitPage($page, $number);
        }

		$items = $this->dbService->fetchObjects('Expense', $select);

		return $items;
    }
    
    /**
     * Get the total number of expenses for a given where clause
     *
     * @param array $where
     * @return int
     */
    public function getExpensesCount($where)
    {
        return $this->dbService->getObjectCount($where, 'Expense');
    }
    
/**
     * Get the expenses for a given user (optional) and client (optional)
     * for the passed in time period
     * 
     * @param string $from
     * @param string $to
     * @param User $user
     * @param Client $client
     */
    public function getDynamicExpenseReport($from, $to, $user=null, $client=null)
    {
        
        $where = array('expensedate >= ' => $from, 'expensedate <= ' => $to);
        $where['status='] = Expense::APPROVED;
        
        if ($user) {
            $where['expense.username='] = $user->getUsername();
        }

        if ($client) {
            $where['expense.clientid='] = $client->id;
        }
        
        return $this->getExpenses($where);
    }

    /**
     * Get the list of expense report objects
     */
    public function getExpenseReports($where = array())
    {
        return $this->dbService->getObjects('ExpenseReport', $where, 'id desc');
    }
    
    /**
     * Lock an expense report
     */
    public function lockExpenseReport(ExpenseReport $report, $user = null)
    {
        $clientid = $report->clientid;
        if ($report->projectid) {
            $clientid = null;
        }
        $start = date('Y-m-d 00:00:00', strtotime($report->from));
        $end = date('Y-m-d 23:59:59', strtotime($report->to));

        $where = array('expensedate >= ' => $start, 'expensedate <= ' => $end);
        $where['status='] = Expense::APPROVED;

        if ($clientid) {
            $where['expense.clientid='] = $clientid;
	        if ($report->projectid) {
	            $where['expense.projectid='] = $report->projectid;
	        }
        } else if (mb_strlen($report->username)) {
            $where['expense.username='] = $report->username;
        }

        $expenses = $this->getExpenses($where);

        try {
            $reportIdField = mb_strlen($report->username) > 0 ? 'userreportid' : 'expensereportid'; 
	        $this->dbService->beginTransaction();
	        
	        $total = 0;
	        foreach ($expenses as $expense) {
	            if ($expense->$reportIdField > 0) {
	                $this->log->debug("Expense #$expense->id is already part of report #".$expense->$reportIdField);
	                continue;
	            }
	            // now, if it was a user that this report is locked against, we need
                // to set the userreportid field instead of the expensereportid
	            $expense->$reportIdField = $report->id;
	            
	            za()->log("Set $expense->id $reportIdField to ".$expense->$reportIdField);

	            $this->dbService->saveObject($expense);
	            $total += $expense->amount;
	        }

	        $report->locked = 1;
	        $report->total = $total;
	        $this->dbService->saveObject($report);
	        
	        $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            throw $e; 
        }
    }
    
    /**
     * As above, this goes through and sets all expense entries 
     * that have been locked against a given report to being
     * unlocked
     *
     * @param ExpenseReport $report
     */
    public function unlockExpenseReport(ExpenseReport $report)
    {
        try {
            $this->dbService->beginTransaction();
            
            $this->dbService->update('expense', array('expensereportid'=>0), 'expensereportid='.(int) $report->id);
            $this->dbService->update('expense', array('userreportid'=>0), 'userreportid='.(int) $report->id);
            $report->locked = 0;
            $this->dbService->saveObject($report);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            throw $e;
        }
    }
    
    /**
     * Marks all expenses in an expense report as paid
     */
    public function markPaidExpenses(ExpenseReport $report)
    {
    try {
            $this->dbService->beginTransaction();
            
            $this->dbService->update('expense', array('paiddate'=>$report->paiddate), 'expensereportid='.(int) $report->id);
            $this->dbService->update('expense', array('paiddate'=>$report->paiddate), 'userreportid='.(int) $report->id);

            // Get all the expenses affected, and notify the user involved that it's been paid

            // If a username is set on the report, it's based around user expenses, 
            // so just get those
            $expenses = array();
            if (mb_strlen($report->username)) {
                $expenses = $this->getExpenses(array('userreportid='=>$report->id));
            } else {
                $expenses = $this->getExpenses(array('expensereportid='=>$report->id));
            }
            
            // Keep a map of user -> expenses for that user to limit the number
            // of emails to send
            $userExpenseMap = new ArrayObject();
            foreach ($expenses as $expense) {
                /* @var $expense Expense */
                
                $user = $this->userService->getUserByField('username', $expense->username);
                $userExpenses = ifset($userExpenseMap, $user->username, array());
                $userExpenses[] = $expense;
                $userExpenseMap[$user->username] = $userExpenses;
            }

            foreach ($userExpenseMap as $username => $expenses) {
                // create the email for this user and send it away
                $msg = new TemplatedMessage('expense-paid.php', array('expenses'=>$expenses));
                $this->notificationService->notifyUser('Expenses Paid', $username, $msg);
                $this->log->debug("Sent email to $username");
            }
            
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            throw $e;
        }
    }

    /**
     * Get all the files for a given expense
     * @return ArrayObject
     */
    public function getExpenseFiles(Expense $expense)
    {
        $client = $this->clientService->getClient($expense->clientid);
        
        if (!$client) {
            // throw new Exception("Invalid expense for attaching files to");
            return array();
        }
        $path = 'Expenses/'.$expense->id;

        $files = $this->fileService->listDirectory($path);
        
        return $files;
    }

    /**
     * Set the status of an expense.
     */
    public function setExpenseStatus(Expense $expense, $status)
    {
        $expense->status = $status;
        $expense->approver = za()->getUser()->getUsername();
        
        $this->dbService->beginTransaction();
        $this->dbService->saveObject($expense);
        $this->trackerService->track('expense-status-changed', $expense->approver.' set status to '.$status);
        $this->dbService->commit();
    }
}
?>