<?php

include_once 'extensions/expenses/model/Expense.php';
include_once 'extensions/expenses/model/ExpenseReport.php';
include_once 'model/Client.php';

class ExpenseController extends BaseController
{
    /**
     * @var ExpenseService
     */
    public $expenseService;
    
    /**
     * ClientService
     * 
     * @var ClientService
     */
    public $clientService;
    
    /**
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * UserService
     * @var UserService
     */
    public $userService;
    
    public function editAction($model=null)
    {
    	if ($this->_getParam('clientid')){
	        $this->view->client = $this->clientService->getClient($this->_getParam('clientid'));
    	}
        else {
        	$ownerId = za()->getConfig('owning_company');
        	$this->view->client = $this->clientService->getClient($ownerId);
        }
        
        $this->view->locations = $this->expenseService->getExpenseLocations();
        $this->view->users = $this->userService->getUserList();
        $this->view->defaultProjectid = za()->getConfig('default_expense_project');
        $this->view->clients = $this->clientService->getClients();
        $this->view->projects = new ArrayObject();

        // Figure out the stuff for displaying the files
        if ($model == null) {
            if ((int) $this->_getParam('id')) {
                $this->view->model = $this->byId(); //  $this->dbService->getById((int)$this->_getParam('id'), $modelType);
                $this->view->client = $this->clientService->getClient($this->view->model->clientid); 
            } else {
                $this->view->model = new Expense();
            }
            $this->view->files = array();
        } else {
            $this->view->model = $model;
                $this->view->client = $this->clientService->getClient($this->view->model->clientid); 
        }
 
        if ($this->view->client) {
			$this->view->projects = $this->projectService->getProjectsForClient($this->view->client);
            // get the support project at the very least
			if (!$this->view->defaultProjectid) {
				$project = $this->clientService->getClientSupportProject($this->view->client);
				$this->view->defaultProjectid = $project->id;
			}
        }


        $this->view->categories = $this->view->model->constraints['atocategory']->getValues();        
        $this->view->expenseTypes = $this->view->model->constraints['expensetype']->getValues();
        $this->view->expenseCategories = $this->view->model->constraints['expensecategory']->getValues();
        
        if ($this->view->model->id) {
            $this->view->files = $this->expenseService->getExpenseFiles($this->view->model);
            $path = 'Expenses/'.$this->view->model->id;
	        $this->view->filePath = $path;
        }
        $this->renderView('expense/edit.php');
    }
    
    /**
     * Edit an expense report
     */
    public function editreportAction($model=null)
    {
        $cid = $this->_getParam('clientid');
        $client = null;
        if ($cid) {
            $client = $this->byId($cid, 'Client');
        }
        $user = $this->userService->getUserByField('username', $this->_getParam('username'));

        if (!$user && !$client) {
            throw new Exception("Must specify a client or user");
        }
        
        $this->view->expenses = array();
        
        if ($client) {
            $this->view->client = $client;
	        $this->view->projects = $this->projectService->getProjects(array('clientid=' => $cid));
	        
	        
        } else {
            $this->view->client = new Client();
            $this->view->projects = array();

        }
        
        if ($user) {
            $this->view->user = $user;
        } else {
            $this->view->user = new CrmUser();
        }
        
        if ($model == null) {
            if ((int) $this->_getParam('id')) {
                $this->view->model = $this->byId(null, 'ExpenseReport'); //  $this->dbService->getById((int)$this->_getParam('id'), $modelType);
                if ($client) {
        	        $this->view->expenses = $this->expenseService->getExpenses(array('expensereportid='=>$this->view->model->id));
                } else {
                    $this->view->expenses = $this->expenseService->getExpenses(array('userreportid='=>$this->view->model->id));
                }
            } else {
                $this->view->model = new ExpenseReport();
            }
        } else {
            $this->view->model = $model;
        }
        
        $this->view->clients = $this->clientService->getClients();
        $this->view->users = $this->userService->getUserList();
        $this->renderView('expense/editreport.php');
    }
    
    /**
     * Auto create a report for a given month
     * 
     */
    public function monthreportAction()
    {
        $user = $this->userService->getUserByField('username', $this->_getParam('username'));
        $client = $this->clientService->getClient($this->_getParam('clientid'));
        if ($user == null && $client == null) {
            throw new Exception("User or client not found");
        }

        $startDate = date('Y-m-01', strtotime($this->_getParam('month')));
        $endDate = date('Y-m-d', strtotime('-1 second', strtotime('+1 month', strtotime(date('Y-m-01 00:00:00', strtotime($this->_getParam('month')))))));
        
        $report = new ExpenseReport();
        $titleName = $user != null ? $user->username : $client->title;
        $report->title = "Expenses for $titleName for ".date('F Y', strtotime($startDate));
        $report->from = $startDate;
        $report->to = $endDate;
        if ($user) {
            $report->username = $user->username;
        }
        if ($client) {
            $report->clientid = $client->id;
        }

        $report = $this->expenseService->saveReport($report);
//         $this->expenseService->lockExpenseReport($report);
        if ($user) {
            $this->redirect('expense', 'listforuser', array('username'=>$user->username));
        } else {
            $this->redirect('client', 'view', array('id'=>$client->id, '#expenses'));
        }
    }

    
    /**
     * Save a report.
     */
    public function savereportAction()
    {
        // If the user was set, don't set the client or project
        if (mb_strlen($this->_getParam('username'))) {
            $this->_setParam('clientid', 0)->_setParam('projectid', 0);
        }

        parent::saveAction('ExpenseReport');
    }
    
    protected function savefailedAction($model)
    {
        if ($model instanceof ExpenseReport) {
            $this->editReportAction($model);
        } else {
            parent::saveFailedAction($model);
        } 
    }
    
    protected function filterParams($params)
    {
        return $params;
    }
    
    /**
     * List all the expenses and expense reports. 
     */
    public function listAction()
    {
        $client = $this->clientService->getClient($this->_getParam('clientid')); 
        if ($client == null) {
            $this->listForUserAction();
            return;
        }

        $this->view->client = $client;
        
        $this->view->expenses = $this->expenseService->getExpenses(array('expense.clientid='=>$client->id));
        $this->view->reports = $this->expenseService->getExpenseReports(array('expensereport.clientid='=>$client->id));
        
        $this->renderView('expense/list.php');
    }
    
    public function listforuserAction()
    {
        $user = $this->userService->getUserByField('username', $this->_getParam('username')); 
        if ($user == null) {
            $user = za()->getUser();
        }

        $this->view->user = $user;
        
        $this->view->expenses = $this->expenseService->getExpenses(array('expense.username='=>$user->username));
        $this->view->reports = $this->expenseService->getExpenseReports(array('expensereport.username='=>$user->username));
        
        $this->renderView('expense/list.php');
    }

    /**
     * Will automatically attempt to save an object when
     * called
     */
    public function saveAction($modelType='')
    {
        $model = null;
        try {
            $params = $this->filterParams($this->_getAllParams());
            $model = $this->expenseService->saveExpense($params);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $model = new Expense();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        }

        $this->onModelSaved($model);
    }
    
    protected function onModelSaved($model) 
    {
        if ($model instanceof ExpenseReport) {
            // Check to see if the expense date is set. 
            if (!empty($model->paiddate)) {
                // make sure to update all child expenses to mark when it was
                // paid
                $this->expenseService->markPaidExpenses($model);
            }
            
            if ($this->_getParam('username')) { 
                $this->redirect('expense', 'listforuser', array('username'=>$this->_getParam('username')));                
            } else { 
                $this->redirect('client', 'view', array('id' => $this->_getParam('clientid'), '#expenses'));
            }
        } else {
	        /*if ($model->clientid) {
	            $this->redirect('client', 'view', array('id'=>$model->clientid, '#expenses'));            
	        } else {*/
            $this->redirect('expense', 'listforuser', array('username'=>$model->username));            
	        //}
        }
    }
    
    /**
     * Deletes the specified object. 
     */
    public function deletereportAction()
    {
        if ((int) $this->_getParam('id')) {
            $model = $this->byId(null, 'ExpenseReport'); 
            if ($model) {
                $this->expenseService->unlockExpenseReport($model);
                $this->dbService->delete($model);
            }
        } else { 
            throw new Exception("No object specified");
        }

        $this->onModelSaved($model);
    }
    
    /**
     * This locks a timesheet (and all its records) off so that those 
     * records cannot be used in other timesheets. 
     */
    public function lockAction()
    {
        $user = $this->userService->getUserByField('username', $this->_getParam('username'));
        $client = $this->clientService->getClient($this->_getParam('clientid'));
        if ($user == null && $client == null) {
            throw new Exception("User or client not found");
        }
        
        $item = $this->byId(null, 'ExpenseReport');
        $this->expenseService->lockExpenseReport($item);

        if ($user) {
            $this->redirect('expense', 'listforuser', array('username'=>$user->username));
        } else if ($client) {
            $this->redirect('client', 'view', array('id'=>$client->id, '#expenses'));
        } else {
            $this->redirect('expense', 'editreport', array('id'=>$item->id, 'clientid'=>$item->clientid, 'username'=>$item->username));
        } 
    }
    
    /**
     * This unlocks a timesheet (and all its records) 
     */
    public function unlockAction()
    {
        $item = $this->byId(null, 'ExpenseReport');
        $this->expenseService->unlockExpenseReport($item);
        $this->redirect('expense', 'editreport', array('id'=>$item->id, 'clientid'=>$item->clientid, 'username'=>$item->username));
    }
    
    /**
     * View the expenses for a given user / client
     */
    public function viewAction()
    {
        $view = new CompositeView();
        $view->addScriptPath('extensions/expenses/views');
        
        $report = $this->byId(null, 'ExpenseReport');
        
        $client = null;
        $user = null;
        
        $expenses = array();
        // we either have a fixed report, or we have a dynamic one
        if ($report) {
            if (mb_strlen($report->username)) {
                $expenses = $this->expenseService->getExpenses(array('userreportid='=>$report->id));
                $user = $this->userService->getUserByField('username', $report->username);
            } else {
                $expenses = $this->expenseService->getExpenses(array('expensereportid='=>$report->id));
                $client = $this->clientService->getClient($report->clientid);
            }
            
            $view->start = $report->from;
            $view->end = $report->to;
        } else {
            $client = $this->clientService->getClient($this->_getParam('clientid'));
	        $user = $this->userService->getUserByField('username', $this->_getParam('username'));
	        $view->user = $user;
        
	        $start = $this->_getParam('start', $this->_getParam('start', $this->calculateDefaultStartDate()));
	        $end = $this->_getParam('end', $this->_getParam('end', $this->calculateDefaultEndDate()));
	        $expenses = $this->expenseService->getDynamicExpenseReport($start, $end, $user, $client);
	        $view->start = $start;
            $view->end = $end;
        }

        $view->expenses = $expenses;
        $view->client = $client;
        $view->user = $user;
        $view->report = $report;
        
        $view->mode = $this->_getParam('pdf') ? 'pdf' : 'html';
 
        $content = $view->render('expense/view.php');
        
        if ($this->_getParam('pdf')) {
            ini_set('memory_limit', '32M');
            
            include_once "dompdf/dompdf_config.inc.php";
            include_once "dompdf/include/dompdf.cls.php";
            include_once "dompdf/include/frame_tree.cls.php";
            include_once "dompdf/include/stylesheet.cls.php";
            include_once "dompdf/include/frame.cls.php";
            include_once "dompdf/include/style.cls.php";
            include_once "dompdf/include/attribute_translator.cls.php";
            include_once "dompdf/include/frame_factory.cls.php";
            include_once "dompdf/include/frame_decorator.cls.php";
            include_once "dompdf/include/positioner.cls.php";
            include_once "dompdf/include/block_positioner.cls.php";
            include_once "dompdf/include/block_frame_decorator.cls.php";
            include_once "dompdf/include/frame_reflower.cls.php";
            include_once "dompdf/include/block_frame_reflower.cls.php";
            include_once "dompdf/include/frame_reflower.cls.php";
            include_once "dompdf/include/text_frame_reflower.cls.php";
            include_once "dompdf/include/canvas_factory.cls.php";
            include_once "dompdf/include/canvas.cls.php";
            include_once "dompdf/include/abstract_renderer.cls.php";
            include_once "dompdf/include/renderer.cls.php";
            include_once "dompdf/include/cpdf_adapter.cls.php";
            include_once "dompdf/include/font_metrics.cls.php";
            include_once "dompdf/include/block_renderer.cls.php";
            include_once "dompdf/include/text_renderer.cls.php";
            include_once "dompdf/include/image_cache.cls.php";
            include_once "dompdf/include/text_frame_decorator.cls.php";
            include_once "dompdf/include/inline_positioner.cls.php";
            include_once "dompdf/include/page_frame_reflower.cls.php";
            include_once "dompdf/include/page_frame_decorator.cls.php";
            include_once "dompdf/include/table_frame_decorator.cls.php";
            include_once "dompdf/include/cellmap.cls.php";
            include_once "dompdf/include/table_frame_reflower.cls.php";
            include_once "dompdf/include/table_row_frame_decorator.cls.php";
            include_once "dompdf/include/null_positioner.cls.php";
            include_once "dompdf/include/table_row_frame_reflower.cls.php";
			include_once "dompdf/include/table_cell_frame_decorator.cls.php";
			include_once "dompdf/include/table_cell_positioner.cls.php";
			include_once "dompdf/include/table_cell_frame_reflower.cls.php";
			include_once "dompdf/include/table_row_group_frame_decorator.cls.php";
			include_once "dompdf/include/table_row_group_frame_reflower.cls.php";
			include_once "dompdf/include/table_cell_renderer.cls.php";
			include_once "dompdf/include/inline_frame_decorator.cls.php";
			include_once "dompdf/include/inline_frame_reflower.cls.php";
			include_once "dompdf/include/image_frame_decorator.cls.php";
			include_once "dompdf/include/image_frame_reflower.cls.php";
			include_once "dompdf/include/inline_renderer.cls.php";
			include_once "dompdf/include/image_renderer.cls.php";
			include_once "dompdf/include/dompdf_exception.cls.php";
			
			$dompdf = new DOMPDF();
			// $dompdf->set_paper('letter', 'landscape');
			$dompdf->load_html($content);
			$dompdf->render();
			
			$name = "expenses-".date('Y-m-d', strtotime($view->start)).'-to-'.date('Y-m-d', strtotime($view->end)).'.pdf';
			$dompdf->stream($name);

        } else {
            echo $content;
        }
    }
    
    /**
     * Change the status of an expense application
     */
    public function changestatusAction()
    {
        $expense = $this->byId();
        if (!$expense) {
            throw new Exception("Invalid leave application specified");
        }
        
        $status = $this->_getParam('status', 'deny');
        
        $status = $status == 'approve' ? Expense::APPROVED : Expense::DENIED;
        
        try {
            $this->expenseService->setExpenseStatus($expense, $status);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
        }

        $this->redirect('client', 'view', array('id'=>$expense->clientid, '#expenses'));
    }
    
    public function domultipleAction()
    {
        $selected = $this->_getParam('selected');
        $ids = split(',', $selected);
        $status = mb_strtolower($this->_getParam('expenseaction'));
        $user = $this->_getParam('user');
        $client = $this->_getParam('clientid');
        
        $status = $status == 'approve' ? Expense::APPROVED : Expense::DENIED;
        foreach ($ids as $id) {
            $expense = $this->byId($id);
            if ($expense != null) {
                $this->expenseService->setExpenseStatus($expense, $status);
            }
        }
        
        if ($user) {
            $this->redirect('expense', 'listforuser', array('username' => $user));
        } else {
            $this->redirect('client', 'view', array('id'=>$client, '#expenses'));
        }
    }
    
    /**
     * Calculates a start date as the most recent monday
     *
     */
    private function calculateDefaultStartDate()
    {
        $now = time();
        while (date('d', $now) != '1') {
            $now -= 86400;
        }

        return date('Y-m-d', $now);
    }
    
/**
     * Calculates a start date as the most recent monday
     *
     */
    private function calculateDefaultEndDate()
    {
        $now = time();
        // go forward to first day of next month, then back one
        while (date('d', $now) != '1') {
            $now += 86400;
        }

        return date('Y-m-d', $now - 86400);
    }
}
?>