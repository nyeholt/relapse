<?php

include_once 'controllers/ProjectController.php';
include_once 'model/Project.php';

class External_ProjectController extends NovemberController 
{
    /**
     * @var ClientService
     */
    public $clientService;
    
    public $projectService;
    
    public $fileService;
    
    /**
     * Check that the user can access the requested project
     */
    public function preDispatch()
    {
        $client = $this->clientService->getUserClient(za()->getUser());
        if ($client != null) {
            // Set the client
            $this->_setParam('clientid', $client->id);
        }

        // make sure that the user is doing something they're allowed to do
        $id = $this->_getParam('id');
        if ($id) {
            $obj = $this->byId();
            if (!$obj || $client == null || $obj->clientid != $client->id) {
            	$this->log->warn("Client is ".$client->title." $client->id  and $obj->clientid");
                $this->requireLogin();
            }
        }
    }
    
    /**
     * List only projects for this user's client
     */
    public function indexAction()
    {
        $this->requireLogin();
    }

    /**
     * View a project.
     *
     */
    public function viewAction()
    {
        
        $project = $this->projectService->getProject((int) $this->_getParam('id'));
        if ($project == null) {
            $this->flash("Project not found");
            $this->renderView('error.php');
            return;
        }
        
        if ($project->isprivate) {
        	$this->flash('That project cannot be viewed');
        	$this->redirect('index');
        }
        
        $totalCount = $this->projectService->getTaskCount(array('projectid ='=>$project->id, 'complete=' => 0));
        
        $this->view->taskPagerName = 'ptasks';
        $currentPage = ifset($this->_getAllParams(), $this->view->taskPagerName, 1);
        
        $this->view->totalTasks = $totalCount;
        $this->view->taskListSize = za()->getConfig('project_task_list_size');
        $this->view->displayedTasks = $this->projectService->getTasks(array('projectid ='=>$project->id, 'complete=' => 0), 'due desc', $currentPage, za()->getConfig('project_task_list_size'));
        
        $totalCompleted = $this->projectService->getTaskCount(array('projectid ='=>$project->id, 'complete=' => 1));
        $this->view->completedPagerName = 'ctasks';
        $currentPage = ifset($this->_getAllParams(), $this->view->completedPagerName, 1);
        
        $this->view->totalCompleted = $totalCompleted;
        $this->view->completedTasks = $this->projectService->getTasks(array('projectid ='=>$project->id, 'complete=' => 1), 'due desc', $currentPage, za()->getConfig('project_task_list_size'));
        
        
        $this->view->project = $project;
        $this->view->title = $project->title;
        $this->renderView('project/external-view.php');
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
        $this->view->projects = $this->projectService->getProjects(array('parentid='=>$project->id, 'ismilestone='=>0, 'isprivate='=>0), 'created desc');        
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
        
        // now create a sub-project and redirect to it
        $newChild = $this->projectService->createSubProject($project, $this->_getParam('newTitle'));
        $this->redirect('project', 'view', array('id'=>$newChild->id)); 
    }
    
    /**
     * Override the edit action to supply some selectable relationships
     *
     * @param MappedObject $model
     */
    public function editAction($model=null)
    {
        $this->requireLogin();
    }
    
    public function saveAction()
    {
        $this->requireLogin();
    }
    
    public function listAction()
    {
        $client = $this->clientService->getClient((int) $this->_getParam('clientid'));
        if (!$client) {
            echo "Failed loading projects";
            return;
        }

        $this->view->hideHeader = true;
        $this->view->client = $client;

        $totalCount = $this->projectService->getProjectCount(array('clientid=' => $client->id, 'parentid='=> 0, 'isprivate='=>0));

        $this->view->pagerName = 'ptasks';
        $currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
        
        $this->view->totalProjects = $totalCount;
        $this->view->projectListSize = za()->getConfig('project_task_list_size');
        
        $this->view->projects = $this->projectService->getProjects(array('clientid=' => $client->id, 'parentid='=> 0, 'isprivate='=>0));

        $this->renderRawView('project/index.php');
        /*
        $client = $this->clientService->getClient((int) $this->_getParam('clientid'));
        if (!$client) {
            echo "Failed loading projects";
            return;
        }
        $this->view->hideHeader = true;
        $this->view->client = $client;
        $this->view->projects = $this->projectService->getProjects(array('clientid=' => $client->id, 'parentid='=>0, 'isprivate='=>0));

        $this->renderRawView('project/index.php');*/
    }
    
    /**
     * 'Delete' a project
     *
     */
    public function deleteAction()
    {
        $this->requireLogin();
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

        $content = '';

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


        $this->getResponse()->appendBody($content); 
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
    }
}
?>