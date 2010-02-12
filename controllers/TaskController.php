<?php
class TaskController extends BaseController 
{
    /**
     * Project Service
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * Notification service
     *
     * @var NotificationService
     */
    public $notificationService;

    /**
     * @var UserService
     */
    public $userService;

    /**
     * @var TagService
     */
    public $tagService;
    
    /**
     * @var ClientService
     */
    public $clientService;

    /**
     * Which actions are to be validated
     */
    protected $validateActions = array('saveaction');
    
    /**
     * @var SearchService
     */
    public $searchService;
    
    /**
     * The ItemLinkService
     *
     * @var ItemLinkService
     */
    public $itemLinkService;

    /**
     * Get a list of all the current user's tasks
     * 
     */
	public function listAction()
    {
    	$unassignedProject = $this->projectService->getUnassignedTaskProject();

    	if ($this->_getParam('all')) {
    		$this->view->tasks = $this->projectService->getTasks(array('projectid <>' => $unassignedProject->id, 'complete='=>0));
	    	$this->view->unassignedTasks = $this->projectService->getTasks(array('projectid=' => $unassignedProject->id));
    	} else {
	    	$this->view->tasks = $this->projectService->getUserTasks(za()->getUser(), array('projectid <>' => $unassignedProject->id, 'complete='=>0));
	    	$this->view->unassignedTasks = $this->projectService->getUserTasks(za()->getUser(), array('projectid=' => $unassignedProject->id)); 
    	}

    	$this->renderView('task/list.php');
    }
    
    public function viewAction()
    { 
    	$this->editAction();
    }

    /**
     * Get a bunch of data prepared for the view
     */
    protected function prepareForEdit()
    {
        if ($this->view->model == null) {
            throw new Exception("Task not found!");
        }

        // check the existence of the client to add this contact to
        $pid = (int) $this->_getParam('projectid') ? (int) $this->_getParam('projectid') : $this->view->model->projectid;
        $project = $this->projectService->getProject($pid);
        
        if ($project == null) {
/*            $this->flash("Specified project not found");
            $this->renderView('error.php');
            return;*/
            $project = new Project();
        }

        $this->view->project = $project;
        $this->view->projectUsers = $this->projectService->getProjectUsers($project);
        if ($project->id) {
            $this->view->projects = $this->projectService->getProjectsForClient($project->clientid);
        } else {
            $this->view->projects = new ArrayObject();
        }

        $this->view->activeTasks = $this->projectService->getActiveProjectTasks($project);
        
        $this->view->categories = $this->view->model->constraints['category']->getValues();
        $this->view->clients = $this->clientService->getClients();
        
        $this->view->model->tags = "";
	    if ($this->view->model->id) {
            $this->view->notes = $this->notificationService->getNotesFor($this->view->model);
            $this->view->existingWatch = $this->notificationService->getWatch(za()->getUser(), $this->view->model->id, 'Task');

            $this->view->allUsers = $this->userService->getUserList();
            $this->view->subscribers = $this->notificationService->getSubscribers($this->view->model->id, 'Task');

            $tags = $this->tagService->getItemTags($this->view->model);
            $tagStr = "";
            $sep = "";
            foreach ($tags as $tag) {
                $tagStr .= $sep.$tag->tag;
                $sep = ',';
            }
            $this->view->model->tags = $tagStr;
            
            // get all the issues that triggered this task
			$this->view->issues = $this->itemLinkService->getLinkedItemsOfType($this->view->model, 'to', 'Issue');
			$this->view->features = $this->itemLinkService->getLinkedItemsOfType($this->view->model, 'to', 'Feature');
			
			// get selectable features based on the milestont this project is in. 
			$this->view->selectableFeatures = $project->getFeatures();	
			
			$client = $this->clientService->getClient($project->clientid);
			// get all the requests for the given client

			$this->view->selectableRequests = $client->getIssues();
        }
        
    }
    
    public function quickCreateAction()
    {
    	$params = array('title' => $this->_getParam('title'));
    	$params['userid'] = array(za()->getUser()->getUsername());
    	$task = $this->projectService->saveTask($params);
    	
    	// return the ID of the created task
		echo $task->id;
    }
    
    /**
     * Will automatically attempt to save an object when
     * called
     */
    public function saveAction($modelType='')
    {
        $model = null;
        try {
            $this->dbService->beginTransaction();
            
            $params = $this->filterParams($this->_getAllParams());
            $model = $this->projectService->saveTask($params, true);
            
            // Now save the tags for that model
            $tagstr = $this->_getParam('tags');
            
            if (!mb_strlen($tagstr)) {
                // go and get some suggested tags!
                if (mb_strlen($model->description)) {
                    $possibles = $this->tagService->suggestTagsFor($model->description);
                    if (count($possibles) > 0) {
                        $tagstr = implode(",", $possibles);
                    }
                }
            }

            if ($tagstr) {
                $this->log->debug("Adding tags $tagstr to task #$model->id");
                $this->tagService->saveTags($model, $tagstr);
            }
            $this->dbService->commit();
        } catch (InvalidModelException $ime) {
            za()->log("Faild saving task :".$ime->getMessage(), Zend_Log::ERR);
            $this->dbService->rollback();
            $this->flash($ime->getMessages());
            $model = new Task();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        } catch (Exception $e) {
            za()->log("Faild saving task :".$e->getMessage(), Zend_Log::ERR);
            $this->dbService->rollback();
            $this->flash($e->getMessage());
            $model = new Task();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        }

        $this->onModelSaved($model);
    }
    
    protected function filterParams($params)
    {
        $params = parent::filterParams($params);
        $assigned = ifset($params, 'userid', null);
        if (!is_array($assigned)) {
            $params['userid'] = array();
        }
        return $params;
    }
    
    /**
     * Called to redirect after saving a model object
     *
     */
    protected function onModelSaved($model)
    {
        $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        
        // after saving a task, we should probably notify the user about it hey?
        
        if (!$this->_getParam('id')) {
            $message = new TemplatedMessage('new-task.php', array('model' => $model));
            $this->notificationService->notifyUser('New task', $model->userid, $message);
            // Create a watch for the creator
            $this->notificationService->createWatch(za()->getUser(), $model->id, 'Task'); 
        }
        
        // Check to see if the assignee has a watch, if not, add one
        foreach ($model->userid as $username) {
            $assignedTo = $this->userService->getByName($username);
	        if ($assignedTo) {
	            $existing = $this->notificationService->getWatch($assignedTo, $model->id, 'Task');
	            if (!$existing) {
	                $this->notificationService->createWatch($assignedTo, $model->id, 'Task'); 
	            }
	        }
        }

		if ($this->_getParam('_ajax')) {
			$this->redirect('task', 'taskactions', array('id'=>$model->id));
		} else {
			$this->redirect('task', 'edit', array('id'=>$model->id));
		}
        
    }

	/**
	 * Display a list of actions that can be taken for a particular task
	 */
	public function taskActionsAction()
	{
		$this->view->model = $this->byId();
		$this->renderRawView('task/task-actions.php');
	}

    /**
     * Returns details about a particular task.
     *
     */
    public function taskDetailAction()
    {
        $this->view->task = $this->projectService->getTask((int) $this->_getParam('id'));
    }

    /**
     * Complete a task
     *
     */
    public function completeAction()
    {
        $task = $this->byId();
        $this->projectService->completeTask($task, za()->getUser());
    }

    /**
     * Delete the specified task
     *
     */
    public function deleteAction()
    {
        $task = $this->byId();
        // delete it
        if (!$this->projectService->deleteTask($task)) {
           $this->flash("Failed deleting ".$task->title); 
        }

        $this->redirect('project', 'view', array('id'=>$task->projectid, '#tasks'));
    }

    /**
     * Import information from a file
     */
    public function importAction()
    {
        $project = $this->byId(null, 'Project');
        
        if (!isset($_FILES['importfile']) && !isset($_FILES['importfile']['tmp_name'])) {
            throw new Exception("Import file not found");
        }

        if (!$project) {
            throw new Exception("Invalid project");
        }

        $this->projectService->importTasks($project, $_FILES['importfile']['tmp_name'], $this->_getParam('importtype'));
        
        $this->redirect('project', 'view', array('id'=>$project->id, '#tasks'));
    }

	/**
	 * Exports a task in either GanttProject or MSProject format
	 * 
	 */
    public function exportAction()
    {
        $project = $this->byId(null, 'Project');
        
        $includeComplete = $this->_getParam('includecompleted', false);
        $type = $this->_getParam('importtype');
        $from = $this->_getParam('from');
        $to = $this->_getParam('to');
        
        $export = $this->projectService->exportTasks($project, $type, $includeComplete, $from, $to);
        
        $template = '';
        switch ($type) {
            case 'gp': 
                $template = 'task/export-gp.php';
                break;
            case 'ms':
                $template = 'task/export-ms.php';
                break;
            default: 
                throw new Exception("Invalid export type");
        }
        
        $this->view->export = $export;
        $this->_response->setHeader('Content-type', 'text/csv');
        $this->_response->setHeader("Content-Disposition", "inline; filename=\"export-$type.csv\"");

        $this->renderRawView($template);
    }
    
    /**
     * Create a new task that's linked from another object
     *
     */
    public function newTaskAction()
    {
        $params = $this->_getAllParams();
        $params['createtype'] = 'Task';
        if (!isset($params['newtaskProjectid']) && ($params['type'] == 'Issue' || $params['type'] == 'Feature')) {
        	$this->flash("No milestone specified!");
        	$this->redirect('issue', 'edit', array('id' => ifset($params, 'id')));
        	return;
        }

        $task = $this->itemLinkService->createNewItem($params);
        $prefix = ifset($params, 'prefix', '');
        $task->title = $prefix . ifset($params, 'tasktitle', $task->title);
        
        $task->projectid = ifset($params, 'projectid', ifset($params, 'newtaskProjectid'));
        if (isset($params['assignto'])) {
        	$task->assignTo($params['assignto']);
        } else {
        	$this->projectService->saveTask($task);
        }
        
        $this->redirect('task', 'edit', array('id'=>$task->id, 'projectid'=>$task->projectid));
    }
    
    /**
     * Get a list of tasks that match the given search string
     */
    public function taskListAction()
    {
        $return = array();
        $query = $this->_getParam('query', '');
        /*if ($this->searchService != null && mb_strlen($query) > 2) {
            $query = "+type:'task' +$query";
            $this->log->debug(__CLASS__.':'.__LINE__.': '.$query);
            $results = $this->searchService->search($query);
            foreach ($results as $hit) {
		        try {
		            $hit->__get('id');
		        } catch (Zend_Search_Lucene_Exception $zse) {
		            $this->searchService->deleteHit($hit);
		            continue;
		        }

                $return[$hit->__get('id')] = $hit->title;
            }
        }*/

        if (mb_strlen($query) > 2) {
            $tasks = $this->projectService->getTasks(array('title like ' => '%'.$query.'%'));
            foreach ($tasks as $task) {
		        
                $return[$task->id] = $task->title;
            }
        }

        
        
        $this->_response->setHeader('Content-type', 'text/javascript');
        echo Zend_Json_Encoder::encode($return);
    }
    
    public function linkFromAction()
    {
        $to = $this->byId();
        $from = $this->byId($this->_getParam('fromid'), $this->_getParam('fromtype'));
        
        if ($to && $from) {
            try {
                $this->itemLinkService->parentChildLink($from, $to);
                $this->flash("Successfully linked ".$from->title." to ".$to->title);
            } catch (Exception $e) {
                $this->flash($e->getMessage());
            }
            $this->_redirect($this->getCallingUrl());
        }
    }
    
    public function removeLinkFromAction()
    {
        $to = $this->byId();
        $from = $this->byId($this->_getParam('fromid'), $this->_getParam('fromtype'));
        
        if ($to && $from) {
            try {
                $this->itemLinkService->deleteLinkBetween($from, $to);
                $this->flash("Successfully removed link from ".$from->title." to ".$to->title);
            } catch (Exception $e) {
                $this->flash($e->getMessage());
            }
            $this->_redirect($this->getCallingUrl());
        }
    }
    
    public function addNoteAction()
    {
        $task = $this->byId();
        if ($task) { 
            $note = $this->_getParam('note');
            $title = 'RE Task #'.$task->id.': '. $this->_getParam('title');

            $note = $this->notificationService->addNoteTo($task, $note, $title);
            // Save the issue so it's mod time is updated
            $this->notificationService->sendWatchNotifications($note, array('controller' => 'task', 'action' => 'edit', 'params'=>array('id'=>$task->id))); 
        }

        $this->redirect('task', 'edit', array('id'=>$task->id, '#notes'));
    }
    
}
?>