<?php 

include_once 'model/Timesheet.php';

class TimesheetController extends NovemberController 
{
    /**
     * The project service
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * ClientService
     *
     * @var ClientService
     */
    public $clientService;
    
    /**
     * Enter description here...
     *
     * @var UserService
     */
    public $userService;
    
    /**
     * TagService
     *
     * @var TagService
     */
    public $tagService;
    
    /**
     * Present a filter form to select timesheets
     */
    public function filterAction()
    {
    	/*
    	 * form needs to include:
    	 * 
    	 * user id
    	 * task category
    	 * client id
    	 * project id
    	 * start date
    	 * end date
    	 */
        $this->view->allUsers = $this->userService->getUserList();
        $task = new Task();
        $this->view->categories = $task->constraints['category']->getValues();
        $this->view->clients = $this->clientService->getClients();
    	 $this->view->projects = new ArrayObject();
        $this->renderView('timesheet/filter.php');    	
    }

    /**
     * Present a filter form to select timesheets
     */
    public function filtersummaryAction()
    {
    	/*
    	 * form needs to include:
    	 * 
    	 * user id
    	 * task category
    	 * client id
    	 * project id
    	 * start date
    	 * end date
    	 */
        $this->view->allUsers = $this->userService->getUserList();
        $task = new Task();
        $this->view->categories = $task->constraints['category']->getValues();
        $this->view->clients = $this->clientService->getClients();
    	 $this->view->projects = new ArrayObject();
        $this->renderView('timesheet/filterSummary.php');    	
    }
    
    public function summaryreportAction() {
    	ini_set('memory_limit', '64M');
        
        // -1 will mean that by default, just choose all timesheet records
        $timesheetid = -1;
        $start = null;
        $end = null;
        $cats = array();

		 if ($this->_getParam('category')){
        	$cats = array($this->_getParam('category'));
        }
        
        $project = $this->_getParam('projectid') ? $this->byId($this->_getParam('projectid'), 'Project') : null;
		
		$client = null;
		if (!$project) {
        	$client = $this->_getParam('clientid') ? $this->byId($this->_getParam('clientid'), 'Client') : null;
		}
		
        $user = $this->userService->getUserByField('username', $this->_getParam('username'));

		$this->view->user = $user;
		$this->view->project = $project;
		$this->view->client = $client ? $client : ( $project ? $this->byId($project->clientid, 'Client'):null);
		
		        
        if (!$start) {
	        // The start date, if not set in the parameters, will be just
	        // the previous monday
	        $start = $this->_getParam('start', $this->calculateDefaultStartDate());
	        // $end = $this->_getParam('end', date('Y-m-d', time()));
	        $end = $this->_getParam('end', date('Y-m-d 23:59:59', strtotime($start) + (6 * 86400)));
        }
        
        // lets normalise the end date to make sure it's of 23:59:59
		$end = date('Y-m-d 23:59:59', strtotime($end));

        $order = 'endtime desc';

        $taskInfoList = $this->projectService->getTimesheetReport($user, $project, $client, $timesheetid, $start, $end, $cats, $order);

		$timeByCat = array();
		foreach ($taskInfoList as $info) {
			$taskTotalTime = 0;
			foreach ($info->days as $day => $time) {
				$taskTotalTime += $time;
			}
			if (!isset($timeByCat[$info->taskcategory])) {
				$timeByCat[$info->taskcategory] = 0;
			}
			$timeByCat[$info->taskcategory] += $taskTotalTime;
		}
		ksort($timeByCat);
		$this->view->timeByCategory = $timeByCat;
	
        $this->view->startDate = $start;
        $this->view->endDate = $end;
        $this->view->params = $this->_getAllParams();
        $this->view->dayDivision = za()->getConfig('day_length', 7.5) / 4; // za()->getConfig('day_division', 2);
        $this->view->divisionTolerance = za()->getConfig('division_tolerance', 20);
        
        $outputformat = $this->_getParam('outputformat');
        if ($outputformat == 'csv') {
        	$this->_response->setHeader("Content-type", "text/csv");
	        $this->_response->setHeader("Content-Disposition", "inline; filename=\"timesheetSummary.csv\"");

	        echo $this->renderRawView('timesheet/summary-csv-export.php');
        } else {
        	$this->renderView('timesheet/summary-html-report.php');
        }
    	
    }
    
    /**
     * Used to display a list of the current week's breakdown 
     */
    public function summaryAction()
    {
    	$timesheetid = -1;

        // Okay, so if we were passed in a timesheet, it means we want to view
        // the data for that timesheet. However, if that timesheet is locked, 
        // we want to make sure that the tasks being viewed are ONLY those that
        // are found in that locked timesheet.
        $timesheet = $this->byId();

        $start = null;
        $end = null;
        $this->view->showLinks = true;
        $cats = array();

        $users = $this->userService->getUserList();

        if (!$start) {
	        // The start date, if not set in the parameters, will be just
	        // the previous monday
	        $start = $this->_getParam('start', $this->calculateDefaultStartDate());
	        // $end = $this->_getParam('end', date('Y-m-d', time()));
	        $end = $this->_getParam('end', date('Y-m-d 23:59:59', strtotime($start) + (6 * 86400)));
        }

        // lets normalise the end date to make sure it's of 23:59:59
		$end = date('Y-m-d 23:59:59', strtotime($end));

        $order = 'endtime desc';

        $this->view->taskInfo = array();

        $project = null;
        if ($this->_getParam('projectid')) {
        	$project = $this->projectService->getProject($this->_getParam('projectid'));
        }
        
        foreach ($users as $user) {
        	$this->view->taskInfo[$user->username] = $this->projectService->getTimesheetReport($user, $project, null, -1, $start, $end, $cats, $order);
        }
        
        $task = new Task();
        
        $this->view->categories = $task->constraints['category']->getValues();
        $this->view->startDate = $start;
        $this->view->endDate = $end;
        $this->view->params = $this->_getAllParams();
        $this->view->dayDivision = za()->getConfig('day_length', 8) / 4; // za()->getConfig('day_division', 2);
        $this->view->divisionTolerance = za()->getConfig('division_tolerance', 20);
        
        $this->renderView('timesheet/user-report.php');
    }
    
    /**
     * Show the interface for creating 
     *
     */
    public function indexAction()
    {
    	ini_set('memory_limit', '64M');
        $validFormats = array('weekly');
        $format = 'weekly'; // $this->_getParam('format', 'weekly');
        
        if (!in_array($format, $validFormats)) {
            $this->flash('Format not valid');
            $this->renderView('error.php');
            return;
        }

        $reportingOn = 'Dynamic';
        
        // -1 will mean that by default, just choose all timesheet records
        $timesheetid = -1;

        // Okay, so if we were passed in a timesheet, it means we want to view
        // the data for that timesheet. However, if that timesheet is locked, 
        // we want to make sure that the tasks being viewed are ONLY those that
        // are found in that locked timesheet.
        $timesheet = $this->byId();

        $start = null;
        $end = null;
        $this->view->showLinks = true;
        $cats = array();

        if ($timesheet) {
            $this->_setParam('clientid', $timesheet->clientid);
            $this->_setParam('projectid', $timesheet->projectid);
            if ($timesheet->locked) {
                $timesheetid = $timesheet->id;
                $reportingOn = $timesheet->title;
            } else {
                $timesheetid = 0; 
                $reportingOn = 'Preview: '.$timesheet->title;
            }
            if (is_array($timesheet->tasktype)) {
            	$cats = $timesheet->tasktype;
            } 

            $start = date('Y-m-d 00:00:01', strtotime($timesheet->from));
            $end = date('Y-m-d 23:59:59', strtotime($timesheet->to)); 
            $this->view->showLinks = false;
        } else if ($this->_getParam('category')){
        	$cats = array($this->_getParam('category'));
        }
        
        
        $project = $this->_getParam('projectid') ? $this->byId($this->_getParam('projectid'), 'Project') : null;
		
		$client = null;
		if (!$project) {
        	$client = $this->_getParam('clientid') ? $this->byId($this->_getParam('clientid'), 'Client') : null;
		}
		
        $user = $this->_getParam('username') ? $this->userService->getUserByField('username', $this->_getParam('username')) : null;
        
		$this->view->user = $user;
		$this->view->project = $project;
		$this->view->client = $client ? $client : ( $project ? $this->byId($project->clientid, 'Client'):null);
		$this->view->category = $this->_getParam('category');
        
        if (!$start) {
	        // The start date, if not set in the parameters, will be just
	        // the previous monday
	        $start = $this->_getParam('start', $this->calculateDefaultStartDate());
	        // $end = $this->_getParam('end', date('Y-m-d', time()));
	        $end = $this->_getParam('end', date('Y-m-d 23:59:59', strtotime($start) + (6 * 86400)));
        }
        
        // lets normalise the end date to make sure it's of 23:59:59
		$end = date('Y-m-d 23:59:59', strtotime($end));

        $this->view->title = $reportingOn;
        
        $order = 'endtime desc';
        if ($format == 'weekly') {
            $order = 'starttime asc';
        }

        $this->view->taskInfo = $this->projectService->getTimesheetReport($user, $project, $client, $timesheetid, $start, $end, $cats, $order);
        
        // get the hierachy for all the tasks in the task info. Make sure to record how 'deep' the hierarchy is too
		$hierarchies = array();

		$maxHierarchyLength = 0;
		foreach ($this->view->taskInfo as $taskinfo) {
			if (!isset($hierarchies[$taskinfo->taskid])) {
				$task = $this->projectService->getTask($taskinfo->taskid);
				$taskHierarchy = array();
				if ($task) {
					$taskHierarchy = $task->getHierarchy();
					if (count($taskHierarchy) > $maxHierarchyLength) {
						$maxHierarchyLength = count($taskHierarchy);
					}
				}
				$hierarchies[$taskinfo->taskid] = $taskHierarchy;
			} 
		}
	
		$this->view->hierarchies = $hierarchies;
		$this->view->maxHierarchyLength = $maxHierarchyLength;

        $this->view->startDate = $start;
        $this->view->endDate = $end;
        $this->view->params = $this->_getAllParams();
        $this->view->dayDivision = za()->getConfig('day_length', 8) / 4; // za()->getConfig('day_division', 2);
        $this->view->divisionTolerance = za()->getConfig('division_tolerance', 20);
        
        $outputformat = $this->_getParam('outputformat');
        if ($outputformat == 'csv') {
        	$this->_response->setHeader("Content-type", "text/csv");
	        $this->_response->setHeader("Content-Disposition", "inline; filename=\"timesheet.csv\"");

	        echo $this->renderRawView('timesheet/csv-export.php');
        } else {
        	$this->renderView('timesheet/'.$format.'-report.php');
        }
    }
    
    /**
     * A view action just redirects to the index for now
     *
     */
    public function viewAction()
    {
        $this->indexAction();
    }
    
    /**
     * Export a timesheet to CSV
     *
     */
    public function exportAction()
    {
        $this->_setParam('outputformat', 'csv');
        $this->indexAction();        
    }
    
    public function summaryreportexportAction()
    {
        $this->_setParam('outputformat', 'csv');
        $this->summaryReportAction();        
    }
    /**
     * List all timesheets available for the given 
     * project/client
     */
    public function listAction()
    {
        $pid = $this->_getParam('projectid');
        $where = array();
        if ($pid) {
            $where['projectid='] = $pid;
        }
        $cid = $this->_getParam('clientid');
        if ($cid) {
            $where['clientid='] = $cid;
        }
        
        $this->view->timesheets = $this->projectService->getTimesheets($where);
        
        $this->renderView('timesheet/list.php');
    }
    
    /**
     * When a timesheet is saved
     *
     * @param Timesheet $model
     */
    public function onModelSaved($model)
    {
        if ($model->projectid) {
            $this->redirect('project', 'view', array('id'=>$model->projectid, '#timesheet'));            
        } else {
            $this->redirect('client', 'view', array('id'=>$model->clientid, '#timesheet'));
        }
    }
    
    /**
     * When a timesheet is deleted
     *
     * @param Timesheet $model
     */
    public function onModelDeleted($model)
    {
        $this->onModelSaved($model);
    }
    
    /**
     * Edit a timesheet
     */
    public function editAction($model=null)
    {
        $pid = $this->_getParam('projectid');
        if ($pid) {
            $this->view->project = $this->byId($pid, 'Project');
        }

        $cid = $this->_getParam('clientid');
        if ($cid) {
            $this->view->client = $this->byId($cid, 'Client');
        }
        
        // bind in the task types
		$task = new Task();
		$this->view->categories = $task->constraints['category']->getValues();
        
        parent::editAction($model);
    }
    
    /**
     * This locks a timesheet (and all its records) off so that those 
     * records cannot be used in other timesheets. 
     */
    public function lockAction()
    {
        $timesheet = $this->byId();
        
        $this->projectService->lockTimesheet($timesheet);
        
        $this->redirect('timesheet', 'edit', array('clientid'=>$timesheet->clientid, 'projectid'=>$timesheet->projectid, 'id'=>$timesheet->id));
    }
    
    /**
     * This unlocks a timesheet (and all its records) 
     */
    public function unlockAction()
    {
        $timesheet = $this->byId();
        $this->projectService->unlockTimesheet($timesheet);
        $this->redirect('timesheet', 'edit', array('clientid'=>$timesheet->clientid, 'projectid'=>$timesheet->projectid, 'id'=>$timesheet->id));
    }
    
    /**
     * Calculates a start date as the most recent monday
     *
     */
    private function calculateDefaultStartDate()
    {
        $now = time();
        while (date('D', $now) != 'Mon') {
            $now -= 86400;
        }

        return date('Y-m-d', $now);
    }
    
    /**
     * Start recording time for a particular task
     */
    public function recordAction()
    {
        $task = $this->projectService->getTask($this->_getParam('id'));
        
        $user = za()->getUser();
        
        $time = time();
        $record = $this->projectService->addTimesheetRecord($task, $user, $time, $time);

        $this->view->task = $task;
        $this->view->record = $record;
        $this->view->relatedFaqs = $this->tagService->getRelatedItems($task);
        
        $this->renderRawView('timesheet/record.php');
    }
    
    /**
     * Update a timesheet
     *
     */
    public function updateAction()
    {
        $endtime = (int) $this->_getParam('endtime');
        $record = $this->byId($this->_getParam('id'), 'TimesheetRecord');
        
        // We need to get the record back from the system in case
        // the record we're timing against has changed. This can happen if
        // a record is timed and goes over a 24hr block, which we want to
        // prevent happening so that we don't have any HUGE blocks of
        // time records. 
        $record = $this->projectService->updateTimesheetRecord($record, $endtime);
        
        $this->view->record = $record;
        $this->view->task = $this->projectService->getTask($this->_getParam('taskid'));
        
        $this->_response->setHeader('Content-type', 'text/javascript');
        $this->renderRawView('timesheet/update.php');
    }
    
    public function insertAction()
    {
        $start = strtotime($this->_getParam('start'));
        $end = strtotime($this->_getParam('end'));
        $task = $this->projectService->getTask($this->_getParam('taskid'));
        $user = za()->getUser();
        
        $this->projectService->addTimesheetRecord($task, $user, $start, $end);
    }
    
    /**
     * Add some time to a task
     *
     */
    public function addtimeAction()
    {
		$start = strtotime($this->_getParam('start', date('Y-m-d')) . ' ' . $this->_getParam('start-time', '12:00') . ':00');
        $end = $start + $this->_getParam('total') * 3600;
        $task = $this->projectService->getTask($this->_getParam('taskid'));
        $user = za()->getUser();

        $this->projectService->addTimesheetRecord($task, $user, $start, $end);

		$reloadUrl = build_url('timesheet','detailedTimesheet', array('taskid' => $task->id));
		
		if ($this->_getParam('_ajax')) {
			echo '<script>
			$("#timesheetdialog").simpleDialog("close");
			$("#timesheetdialog").simpleDialog({url: "'.$reloadUrl.'"});
</script>';
		}
    }
    
    /**
     * Get the timesheet for this project
     *
     */
    public function timesheetAction()
    {
        $project = $this->projectService->getProject($this->_getParam('projectid'));
        $client = $this->clientService->getClient($this->_getParam('clientid'));
        
        if (!$project && !$client) {
            return;
        }
        
        if ($project) {
            $start = date('Y-m-d', strtotime($project->started) - 86400);
            $this->view->tasks = $this->projectService->getSummaryTimesheet(null, null, $project->id, null, null, $start, null);
        } else {
            $start = date('Y-m-d', strtotime($client->created));
            $this->view->tasks = $this->projectService->getSummaryTimesheet(null, null, null, $client->id, null, $start, null);
        }

        $this->renderRawView('timesheet/ajax-timesheet-summary.php');
    }

    /**
     * Get the detailed timesheet for this project.
     *
     */
    public function detailedtimesheetAction()
    {
        $project = $this->projectService->getProject($this->_getParam('projectid'));
        $client = $this->clientService->getClient($this->_getParam('clientid'));
        $task = $this->projectService->getTask($this->_getParam('taskid'));
        $user = $this->userService->getUserByField('username', $this->_getParam('username'));
		
        if (!$project && !$client && !$task && !$user) {
            return;
        }

		if ($task) { 
            $this->view->records = $this->projectService->getDetailedTimesheet(null, $task->id);
        } else if ($project) {
            
            $start = null;
            $this->view->records = $this->projectService->getDetailedTimesheet(null, null, $project->id);
        } else if ($client) {
            
            $start = null;
            $this->view->records = $this->projectService->getDetailedTimesheet(null, null, null, $client->id);
        } else if ($user) {
			$this->view->records = $this->projectService->getDetailedTimesheet($user);
		}

		$this->view->task = $task;
        $this->renderRawView('timesheet/ajax-timesheet-details.php');
    }
    
    public function deleterecordAction()
    {
        $record = $this->byId(null, 'TimesheetRecord');
        $this->projectService->removeTimesheetRecord($record);
    }
}
?>