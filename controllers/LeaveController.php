<?php
include_once 'model/Leave.php';
include_once 'model/LeaveApplication.php';

class LeaveController  extends BaseController 
{
    /**
     * UserService
     *
     * @var UserService
     */
    public $userService;
    
    /**
     * We're never creating leave directly, so die 
     * if there's no associated user
     *
     * @param Leave $model
     */
    public function editAction($model=null)
    {
        if (!(int) $this->_getParam('id')) {
            throw new Exception("Cannot create new Leave details");
        }

        parent::editAction($model);
    }
    
    public function prepareForEdit($model)
    {
    	$this->view->leaveTypes = $model->constraints['leavetype']->getValues();
    }
    
    /**
     * When leave is saved, we want to handle it manually
     * so that we can track who changed it, and what they
     * changed it to. 
     */
    public function saveAction()
    {
        try {
            $model = $this->byId();
            if (!$model) {
                throw new Exception("Failed to communicate");
            }

            $params = $this->filterParams();
            $model = $this->userService->updateLeave($model, $params['days']);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $this->log->debug($ime->getTraceAsString(), Zend_Log::ERR);
            $model = new Leave();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        }

        $this->onModelSaved($model);
    }
    
    /**
     * After saving, redirect back to the user's page
     */
    public function onModelSaved($model)
    {
        $user = $this->userService->getUserByField('username', $model->username);
        
        if (!$user) {
            $this->log->debug(__CLASS__.':'.__LINE__.": Cannot find user $model->username in leave record $model->id");
            $this->redirect('user', 'edit');
        } else {
            $this->redirect('user', 'edit', array('id'=>$user->id));
        }
    }
    
    /**
     * We only delete if the leave item is NOT approved
     */
    public function deleteAction()
    {
        if ((int) $this->_getParam('id')) {
            $model = $this->byId($this->_getParam('id'), 'LeaveApplication'); 
            if ($model->status == LeaveApplication::LEAVE_APPROVED) {
                throw new Exception("Cannot delete approved leave");
            }
            $this->log->debug(__CLASS__.":".__LINE__.": Deleting leave $model->id of status $model->status");
            if ($model) {
                $this->dbService->delete($model);
            }
        } else {
            throw new Exception("No object specified");
        }

        $this->onModelSaved($model);
    }
    
    /**
     * Apply for leave
     */
    public function applyAction($model=null)
    {
        $user = $this->userService->getUser((int) $this->_getParam('userid'));
        
        if (!$user) {
            throw new Exception("You say what?");
        }
        
        if ($model == null) {
            if ((int) $this->_getParam('id')) {
                $this->view->model = $this->byId($this->_getParam('id'), 'LeaveApplication'); //  $this->dbService->getById((int)$this->_getParam('id'), $modelType);
            } else {
                $this->view->model = new LeaveApplication();
            }
        } else {
            $this->view->model = $model;
        }
        
        $this->view->leaveTypes = $this->view->model->constraints['leavetype']->getValues();
        $this->view->user = $user;

        $this->renderView('leave/apply.php');
    }
    
    /**
     * Save a leave application
     */
    public function saveapplicationAction()
    {
        $user = $this->userService->getUser((int) $this->_getParam('userid'));
        
        try {
            $this->_setParam('username', $user->getUsername());
            $params = $this->filterParams();
            $model = $this->userService->applyForLeave($user, $params);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $model = new LeaveApplication();
            $model->bind($this->_getAllParams());
            $this->applyAction($model);
            return;
        }

        $this->onModelSaved($model);
    }

    public function listAction()
    {
        $totalCount = $this->userService->getLeaveApplicationsCount(array());
        
        $this->view->pagerName = 'pager';
        $currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
        
        $this->view->totalItems = $totalCount;
        $this->view->listSize = za()->getConfig('project_list_size');
        
        $this->view->leaveApplications = $this->userService->getLeaveApplications(array(), 'id desc', $currentPage, $this->view->listSize);
        $this->renderView('leave/list.php');
    }
    
    /**
     * Calculates the leave left for a given user
     */
    public function calcAction()
    {
		$username = $this->_getParam('username');
		$user = $this->userService->getUserByField('username', $username);

		$type = $this->_getParam('type', 'Annual');
		$validTypes = array('Annual', 'Sick', 'Long Service');
		if (!in_array($type, $validTypes)) {
			return;
		}

		if ($user == null)  {
			$this->log->warn("Invalid username"); 
			echo 0;
			return;
		} 

		// Calculate how much is left
		$leave = $this->userService->getLeaveForUser($user);
		$accruedLeave = $this->userService->calculateLeave($user);
		$leaveApplications = $this->userService->getLeaveApplicationsForUser($user);

		$leaveTotal = 0;
		$leaveTotals = array();

		foreach ($leaveApplications as $app) {
			if ($app->status == LeaveApplication::LEAVE_APPROVED) {
				$current = ifset($leaveTotals, $app->leavetype, 0);
				$current += $app->days;
				$leaveTotals[$app->leavetype] = $current;
			}
		}
		
		if ($type == 'Annual') {
			echo "About ".sprintf("%d", floor($leave->days + $accruedLeave - ifset($leaveTotals, "Annual", 0)))." days of annual leave available, ".ifset($leaveTotals, "Annual", 0)." taken";
		} else {
			echo ifset($leaveTotals, $type, 0).' days of '.$type.' leave taken';
		}
    }

    /**
     * Change the status of a leave application
     */
    public function changestatusAction()
    {
        $app = $this->byId(null, 'LeaveApplication');
        $status = $this->_getParam('status', 'deny');
        if (!$app) {
            throw new Exception("Invalid leave application specified");
        }
        
        $status = $status == 'deny' ? LeaveApplication::LEAVE_DENIED : LeaveApplication::LEAVE_APPROVED;
        
        try {
            $this->userService->setLeaveStatus($app, $status, (float) $this->_getParam('days', 0));            
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
        }

        $this->redirect('leave', 'list');
    }

}
?>