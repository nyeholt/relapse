<?php

include_once 'model/Project.php';

class ProjectController extends BaseController 
{
    /**
     * Client Service
     *
     * @var ClientService
     */
    public $clientService;
    
    /**
     * The project Service
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * UserService
     *
     * @var  UserService
     */
    public $userService;
    
    /**
     * @var ImageFileService
     */
    public $fileService;
    
    /**
     * @var GroupService
     */
    public $groupService;
    
    /**
     * @var ItemLinkService
     */
    public $itemLinkService;
    
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var CacheService
     */
    public $cacheService;

	/**
	 * @var IssueService
	 */
	public $issueService;
    
    
    public function indexAction()
    {
        $totalCount = $this->projectService->getProjectCount(array('parentid='=>0));
        
        $this->view->pagerName = 'proj-page';
        $currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
        $this->view->letters = $this->projectService->getTitleLetters();
        
        $this->view->totalProjects = $totalCount;
        $this->view->projectListSize = za()->getConfig('project_list_size');
        
        $currentLetter = ifset($this->_getAllParams(), $this->view->pagerName, ifset($this->view->letters, 0, 'A'));

        // Get all projects
        $this->view->projects = $this->projectService->getProjects(array('parentid='=>0, 'title like '=>$currentLetter.'%'), array('clienttitle asc','title asc'), $currentPage, za()->getConfig('project_list_size'));
        $this->renderView('project/index.php');
    }

    /**
     * View a project.
     *
     */
    public function viewAction()
    {
        $__start = getmicrotime();
        
        $project = $this->projectService->getProject((int) $this->_getParam('id'));
        if ($project == null) {
            $this->flash("Project not found");
            $this->renderView('error.php');
            return;
        }

        $this->view->hideHeader = false;
        $totalCount = $this->projectService->getTaskCount(array('projectid ='=>$project->id, 'complete=' => 0));
        
        $this->view->taskPagerName = 'ptasks';
        $currentPage = ifset($this->_getAllParams(), $this->view->taskPagerName, 1);
        
        $this->view->totalTasks = $totalCount;
        $this->view->taskListSize = za()->getConfig('project_task_list_size');
        $this->view->displayedTasks = $this->projectService->getTasks(array('projectid ='=>$project->id, 'complete=' => 0), 'due asc', $currentPage, za()->getConfig('project_task_list_size'));

        $totalCompleted = $this->projectService->getTaskCount(array('projectid ='=>$project->id, 'complete=' => 1));
        $this->view->completedPagerName = 'ctasks';
        $currentPage = ifset($this->_getAllParams(), $this->view->completedPagerName, 1);
        
        $this->view->totalCompleted = $totalCompleted;
        $this->view->completedTasks = $this->projectService->getTasks(array('projectid ='=>$project->id, 'complete=' => 1), 'due asc', $currentPage, za()->getConfig('project_task_list_size'));
        
        $this->view->projectStatusReports = $this->projectService->getStatusReports($project);
        
        $this->view->project = $project;
        $this->view->title = $project->title;
        
        $group = $this->groupService->getGroup($project->ownerid);
        if ($group == null) {
        	$this->log->warn("Invalid project owner $project->ownerid");
        } else {
        	
        }
        
        $this->view->groupusers = $project->getUsers();
        $this->view->users = $this->userService->getUserList();
        $this->view->group = $group;
        
        $this->view->existingWatch = $this->notificationService->getWatch(za()->getUser(), $project);

        $this->view->projectuser = za()->getUser();
        if ($this->_getParam('projectuser')) {
            if ($this->_getParam('projectuser') == 'all') {
                $this->view->projectuser = null; 
            } else {
            	$this->view->projectuser = $this->userService->getUser($this->_getParam('projectuser')); 
            }
        }
        
        if ($this->view->projectuser && !isset($this->view->groupusers[$this->view->projectuser->id])) {
        	$this->view->projectuser = null;
        }

		$where = array('projectid =' => $project->id);
		$new = $this->issueService->getIssues(array('projectid =' => $project->id, 'status =' => Issue::STATUS_NEW));
		if (count($new)) {
			$this->view->newIssues = true;
		} else {
			$this->view->newIssues = false;
		}
		$this->view->issues = $this->issueService->getIssues($where);
        
		za()->recordStat('projectcontroller::setupview', getmicrotime() - $__start);
		$__start = getmicrotime();
        $this->renderView('project/view.php');
        za()->recordStat('projectcontroller::viewrendered', getmicrotime() - $__start);
    }
    
    
    public function childProjectsAction()
    {
        $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        if ($project == null) {
            $this->flash("Project not found");
            $this->renderView('error.php');
            return;
        }

        // Get all the child projects of the given project
        $this->view->projects = $this->projectService->getProjects(array('parentid='=>$project->id, 'ismilestone='=>0), 'created desc');        
        $this->renderView('project/childprojects.php'); 
    }
    
    public function addChildAction()
    {
        $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        if ($project == null) {
            $this->flash("Project not found");
            $this->renderView('error.php');
            return;
        }

        $isMilestone = $this->_getParam('milestone', 0);
        
        $newChild = null;
        if ($isMilestone) {
        	$dateDue = $this->_getParam('due');
        	$newChild = $this->projectService->createMilestone($project, $this->_getParam('newTitle'), $dateDue);
        	$this->redirect('project', 'view', array('id'=>$project->id)); 
        } else {
	        // now create a sub-project and redirect to it
	        $newChild = $this->projectService->createSubProject($project, $this->_getParam('newTitle'));
	        $this->redirect('project', 'view', array('id'=>$newChild->id)); 
        } 
    }
    
    /**
     * Return a list of milestones
     */
    public function listMilestonesAction()
    {
    	$project = $this->byId();
    	/* @var $project Project */
    	$milestones = $project->getMilestones();
    	$stones = array();
    	foreach ($milestones as $stone) {
    		$stones[] = array('id'=>$stone->id, 'title'=>$stone->title);
    		
    	}
    	$stones = Zend_Json_Encoder::encode($stones);
    	echo $stones;
    }

    public function projectGroupAction()
    {
    	$project = $this->projectService->getProject((int) $this->_getParam('id'));
        if ($project == null) {
            $this->flash("Project not found");
            $this->renderView('error.php');
            return;
        }
        
        $group = $this->groupService->getGroup($project->ownerid);
        if ($group == null) {
        	throw new Exception("Invalid group #{$group->ownerid}");
        }
        
        $users = $this->groupService->getUsersInGroup($group, true);
        $groupUsers = new ArrayObject();

        foreach ($users as $user) {
            $groupUsers[$user->id] = $user;
        }

        $this->view->groupusers = $groupUsers;
        $this->view->users = $this->userService->getUserList();
        $this->view->group = $group;
        $this->view->model = $project;
        
        $this->renderRawView('project/group-list.php');
    }
    
    public function updateGroupAction()
    {
    	$usersToAdd = $this->_getParam('groupusers');
        if (!is_array($usersToAdd)) {
        	$usersToAdd = array();
        	
        }

        $group = $this->byId($this->_getParam('groupid'), 'UserGroup');
        $this->groupService->addUsersToGroup($group, $usersToAdd);
        
        $this->redirect('project', 'view', array('id'=>$this->_getParam('id'), '#group-users'));
    }
    

    /**
     * Recalculate the estimated time for the project. 
     *
     */
    public function recalculateAction()
    {
    	$project = $this->byId();
        $this->projectService->updateProjectEstimate($project);
		$this->onModelSaved($project);
    }
    
    /**
     * Override the edit action to supply some selectable relationships
     *
     * @param MappedObject $model
     */
    protected function prepareForEdit($model=null)
    {
    	$clientid = $model && $model->clientid ? $model->clientid : (int) $this->_getParam('clientid'); 
        // check the existence of the client to add this contact to
        $client = $clientid ? $this->clientService->getClient($clientid) : null;
        // check the existence of the client to add this contact to
        
        $this->view->owners = $this->groupService->getGroups();
        $this->view->users = $this->userService->getUserList();
        
        $this->view->client = $client;
        $this->view->clients = $this->clientService->getClients();


		$this->view->projects = $clientid ? $this->projectService->getProjectsForClient($client) : array();
		
        parent::prepareForEdit($model);
    }
    
	/**
     * Saves an object. is declared as a protected method to allow
     * subclasses to override how parameters are saved if needbe. 
     */
    protected function saveObject($params, $modelType)
    {
    	// if it's a project, we'll use the project service to save stuff
		if ($modelType == 'Project') {
			try {
				return $this->projectService->saveProject($params);
			} catch (Exception $e) {
				$this->flash($e->getMessage());
				// try getting the current project if any
				
				return $this->byId();
			}
		} else {
			return parent::saveObject($params, $modelType);
		}
    }
    
    /**
     * Called to redirect after saving a model object
     *
     */
    protected function onModelSaved($model)
    {
		if ($model == null) {
			$this->redirect('project');
		} else {
			$this->redirect('project', 'view', array('id'=>$model->id));
		}
    }
    
    
    /**
     * Load the contacts for a given client id
     *
     */
    public function listAction()
    {
        $client = $this->clientService->getClient((int) $this->_getParam('clientid'));
        if (!$client) {
            echo "Failed loading projects";
            return;
        }

        $this->view->hideHeader = true;
        $this->view->client = $client;
        
        $totalCount = $this->projectService->getProjectCount(array('clientid=' => $client->id, 'parentid='=> 0));
        
        $this->view->pagerName = 'ptasks';
        $currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
        
        $this->view->totalProjects = $totalCount;
        $this->view->projectListSize = za()->getConfig('project_task_list_size');
        
        $this->view->projects = $this->projectService->getProjectsForClient($client);

        $this->renderRawView('project/index.php');
    }
    
    /**
     * 'Delete' a project
     *
     */
    public function deleteAction()
    {
        $project = $this->byId();
        $newParent = $this->byId($this->_getParam('newparent'));
        $this->projectService->deleteProject($project, $newParent);
        if ($project->parentid) {
        	$this->redirect('project', 'view', array('id'=>$project->parentid));
        } else {
        	$this->redirect('client', 'view', array('id'=> $project->clientid));
        }
    }
    
    /**
     * Chart all the tasks in this project
     *
     */
    public function chartAction()
    {
        $project = $this->byId();
        if (!$project) {
            throw new Exception("Invalid project specified");
        }
        $this->view->project = $project;
        $this->view->tasks = $this->projectService->getTasks(array('projectid ='=>$project->id), new Zend_Db_Expr('startdate asc, due asc'));
        $this->renderView('project/chart.php');
    }
    
    /**
     * Called when a user wants to leave a project
     */
    public function leaveAction()
    {
        $project = $this->byId();
        $group = $project->getGroup();
        
        if ($group) {
            $this->groupService->removeFromGroup($group, za()->getUser());
        }
        
        $this->_redirect($this->getCallingUrl());
    }
    
    /**
     * Edit a status report. 
     * 
     * When it's first created, we get the current status, which is used as
     * the 'model' for the editing action. When saved, we need to handle it
     * with care
     *
     */
    public function editReportAction()
    {
        $model = $this->byId(null, 'ProjectStatus');
        
        $pid = isset($model->projectid) ? $model->projectid : $this->_getParam('projectid');
        $project = $this->byId($pid);

        if ($model == null) {
            $model = $this->projectService->getProjectStatus($project);
        }

        $this->view->model = $model;
        $this->view->project = $project;

        $this->renderView('project/editreport.php');
    }
    
    public function saveReportAction()
    {
        // save the edited report or create a new one as of now
        $model = $this->byId(null, 'ProjectStatus');
        $pid = isset($model->projectid) ? $model->projectid : $this->_getParam('projectid');
        $project = $this->byId($pid);

        if ($model == null) {
        	// see if there's a 'to/from' date structure to pass for the status report generation
            $model = $this->projectService->getProjectStatus($project);
        }

        // Save away!
        $model->bind($this->filterParams($this->_getAllParams()));
        
        $this->projectService->saveStatus($model);
        $this->redirect('project', 'editReport', array('id'=>$model->id));
    }
    
    public function generateReportAction()
    {
    	$model = $this->byId(null, 'ProjectStatus');
    	$model->generateStatus();
    	$this->redirect('project', 'editreport', array('id' => $model->id));
    }

    public function deleteStatusReportAction()
    {
        $report = $this->byId(null, 'ProjectStatus');
        $this->dbService->delete($report);
        $this->flash("Deleted report ".$report->title);
        $this->redirect('project', 'view', array('id'=>$report->projectid, '#status'));
    }
    
    /**
     * creates the 'status' view of a project.
     */
    public function statusAction()
    {
        $project = $this->byId();
        $view = new CompositeView();
        
        $projectStatus = $this->_getParam('projectstatus', null);
        $status = null;
        if ($projectStatus) {
            $status = $this->projectService->getStatus($projectStatus);
        } else {
            $status = $this->projectService->getProjectStatus($project, $this->_getParam('period', 7));
        }

        $view->project = $project;
        $view->status = $status;

        $content = $view->render('project/status.php');

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
            include_once "dompdf/include/list_bullet_frame_decorator.cls.php";
            include_once "dompdf/include/list_bullet_positioner.cls.php";
            include_once "dompdf/include/list_bullet_frame_reflower.cls.php";
            include_once "dompdf/include/list_bullet_image_frame_decorator.cls.php";
            include_once "dompdf/include/list_bullet_renderer.cls.php";
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
			$dompdf->load_html($content);
			$dompdf->render();
			
			$date = date('Y-m-d');
			if ($status->created) {
			    $date = date('Y-m-d', strtotime($status->created));
			}
			
			$name = $project->title .' status-'.$date.'.pdf';
			$dompdf->stream($name);

        } else {
            echo $content;
        }
    }
    
    /**
     * Provides an overview of the traceability of this project
     */
    public function traceabilityAction()
    {
        $project = $this->byId();
        $type = $this->_getParam('type');
        $targetId = $this->_getParam('targetid');
        $dir = $this->_getParam('dir');
        
        $selected = null;
        if ($type != null && $targetId != null) {
            $selected = $this->byId($targetId, $type);
        }
        
        if ($project == null && $selected == null) {
            $this->flash("Could not load project");
            $this->redirect('index');
            return;
        }
        
        $items = new ArrayObject();
        // Start with getting all the links from requirements
        if ($selected == null) {
            // Just get all the features to start with
            $featureService = za()->getService('FeatureService');
            /* @var $featureService FeatureService */
            $items = $featureService->getFeatures(array('projectid='=>$project->id));
            
            $this->view->linkedFrom = $project;
        } else {
            $items = $this->itemLinkService->getLinkedItems($selected, $dir);
            $this->view->linkedFrom = $selected;
        }

        $this->view->dir = $dir;
        $this->view->items = $items;
        $this->view->model = $project;
        $this->renderView('project/traceability.php');
    }

    /**
     * List files from a project
     *
     */
    public function fileListAction()
    {
        $project = $this->byId($this->_getParam('projectid'), 'Project');
        $client = $this->byId($project->clientid, 'Client');
        
        $folder = $this->_getParam('folder', '');
        
        $path = 'Clients/'.$client->title.'/Projects/'.$project->title;
    	$projectPath = $path;
        $parent = '';
        if ($folder != null && mb_strlen($folder)) {
            $path = base64_decode($folder);
            $parent = dirname($path);
            if ($path == $projectPath) { 
                $parent = '';
            }
        }

        $content = $this->cacheService->get($path);

        if (!$content) {
	        $files = array();
	        try {
	        	$files = $this->fileService->listDirectory($path);
	        } catch (Exception $e) {
	        	$this->log->err("Failed loading files from $path");
	        }
	
	        $this->view->files = $files;
	        $this->view->project = $project;
	        
	        if ($path == '/') {
	            $this->view->base = '';
	        } else {
	            $this->view->base = trim($path, '/').'/';
	        }
	        $this->view->parentPath = $parent;
	        $content = $this->view->render('project/file-list.php');

	        $this->cacheService->store($path, $content);
        }

        $this->getResponse()->appendBody($content); 
    }

    public function clientProjectsAction()
    {
        $clientid = (int) $this->_getParam('clientid');
        $this->view->projects = $this->projectService->getProjects(array('clientid='=>$clientid));
        
        $projects = array();
        foreach ($this->view->projects as $project) {
            $projects[] = array('id'=>$project->id, 'title'=>$project->title);
        }
        
        echo Zend_Json_Encoder::encode($projects);
        
        // $this->renderRawView('project/clientprojects.php'); 
    }
    

    /**
     * Loads up a project select HTML control that can be ajaxed in 
     * to replace any existing one. 
     */
    public function projectSelectorAction()
    {
    	$clientid = (int) $this->_getParam('clientid');
    	$this->view->fieldName = $this->_getParam('fieldName');
    	$this->view->selectorType = $this->_getParam('selectorType', 'any');
    	$this->view->empty = $this->_getParam('empty', 0);
        $this->view->projects = $this->projectService->getProjectsForClient($clientid);
        $this->renderRawView('project/project-selector.php');
    	$this->view->showMilestones = $this->_getParam('showMilestones');
    }
    
}
?>