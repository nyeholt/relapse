<?php

include_once 'model/Issue.php';

class IssueController extends BaseController 
{
	public static $list_fields = array(
		'id' => 'ID',
		'title' => 'Title',
		'status' => 'Status',
		'severity' => 'Severity',
		'userid' => 'User',
		'updated' => 'Updated',
	);

    /**
     * The project service
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * The client service
     *
     * @var ClientService
     */
    public $clientService;
    
    /**
     * NotificationService
     *
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * The IssueService
     *
     * @var IssueService
     */
    public $issueService;
    
    /**
     * The ItemLinkService
     *
     * @var ItemLinkService
     */
    public $itemLinkService;
    
    /**
     * UserService
     * @var UserService
     */
    public $userService;

    /**
     * The feature service
     *
     * @var FeatureService
     */
    public $featureService;

    /**
     * List all the issues in the system
     */
    public function indexAction()
    {
        $this->bindIssueListViewData(array('status <>'=>Issue::CLOSED_STATUS));

        if ($this->_getParam('__ajax')) {
            $this->renderRawView('issue/list.php');            
        } else {
            $this->renderView('issue/list.php');
        }
    }

	public function listAction()
	{
		if ($this->_getParam('json')) {
			$issues = $this->getIssueList(array('status <>'=>Issue::CLOSED_STATUS));
			$asArr = array();
			foreach ($issues as $issue) {
				$cell = array();
				foreach (self::$list_fields as $name => $display) {
					$cell[] = $issue->$name;
				}
				$row = array(
					'id' => $issue->id,
					'cell' => $cell,
				);
				$asArr[] = $row;
			}
			$obj = new stdClass();
			$obj->page = ifset($this->_getAllParams(), $this->view->pagerName, 1);
			$obj->total = $this->view->totalCount;
			$obj->rows = $asArr;

			$this->getResponse()->setHeader('Content-type', 'text/x-json');
			$json = Zend_Json::encode($obj);
			echo $json;
		} else {
			$this->indexAction();
		}
	}

	/**
	 * Exports issues to a CSV
	 */
    public function csvExportAction()
    {
    	$this->bindIssueListViewData();
    	$this->_response->setHeader('Content-type', 'text/csv');
        $this->_response->setHeader("Content-Disposition", "inline; filename=\"export-issues.csv\"");
        $this->renderRawView('issue/csv.php');
    }
    
    /**
     * Just "edit" a viewed issue
     *
     */
    public function viewAction()
    {
    	$this->editAction();
    }

    /**
     * Override the edit action to supply some selectable relationships
     *
     * @param MappedObject $model
     */
    public function prepareForEdit($model=null)
    {
        if ($this->view->model == null) {
        	$this->flash("Invalid request specified");
        	$this->redirect('error');
        	return;
        }

        $this->view->issueHistory = $this->issueService->getIssueHistory($this->view->model);
        
        $pid = (int) $this->_getParam('projectid');
        $cid = (int) $this->_getParam('clientid');
        
        if ($this->view->model->projectid) {
            $pid = $this->view->model->projectid;
        }
        if ($this->view->model->clientid) {
            $cid = $this->view->model->clientid;
        }
 
        // Which one? 
        if ($pid) {
            $this->view->project = $this->projectService->getProject($pid);
            $this->view->client = $this->clientService->getClient($this->view->project->clientid);
            
            // figure out the releases available for this project
            $this->view->releases = $this->issueService->getProjectReleases($this->view->project);
        } else if ($cid) {
            $this->view->client = $this->clientService->getClient($cid);
            $this->view->releases = array();
        } 
        
        if ($this->view->client != null) {
        	$this->view->categories = $this->issueService->getIssueCategoriesForCompany($this->view->client);
        } else {
        	$this->view->categories = array();
        }
        
        // if it's a new issue, and it's a normal user set it to be private by default
		// User can always specify a different one though
        if (!$this->view->model->id && za()->getUser()->hasRole(User::ROLE_USER)) {
        	$this->view->model->isprivate = true;
        }

        $this->view->users = $this->userService->getUserList();
        
        $this->view->severities = $this->view->model->constraints['severity']->getValues();
        $this->view->types = $this->view->model->constraints['issuetype']->getValues();
        $this->view->statuses = $this->view->model->constraints['status']->getValues();

        if ($this->view->model->id) {
            $this->view->notes = $this->notificationService->getNotesFor($this->view->model);
            $this->view->existingWatch = $this->notificationService->getWatch(za()->getUser(), $this->view->model->id, 'Issue');
            $this->view->userStatuses = $this->view->model->getUserStatuses();
            
            $clientUsers = $this->userService->getUsersForClient($this->view->model->clientid);
            foreach ($this->view->users as $user) {
            	$clientUsers->append($user);
            }
            $this->view->allUsers = $clientUsers;
            $this->view->subscribers = $this->notificationService->getSubscribers($this->view->model->id, 'Issue');
            
            $this->view->project = $this->projectService->getProject($this->view->model->projectid);
            $this->view->client = $this->clientService->getClient($this->view->model->clientid);

            $this->view->files = $this->issueService->getIssueFiles($this->view->model);
            $path = 'Clients/'.$this->view->client->title.'/Issues/'.$this->view->model->id;
	        $this->view->filePath = $path;

	        // Get all the features for this project 
            $this->view->projectFeatures = $this->featureService->getFeatures(array('projectid=' => $this->view->model->projectid));
	        
            $this->view->projectTasks = $this->projectService->getTasks(array('projectid=' => $this->view->project->id), 'title asc');
            
	        $this->view->linkedTasks = $this->itemLinkService->getLinkedItems($this->view->model, 'from', 'Task');
            $this->view->linkedToFeatures = $this->itemLinkService->getLinkedItems($this->view->model, 'from', 'Feature');
            $this->view->linkedFromFeatures = $this->itemLinkService->getLinkedItems($this->view->model, 'to', 'Feature');
	        
        }
        
        $this->view->clients = $this->clientService->getClients();
        if ($this->view->client) {
            $this->view->projects = $this->projectService->getProjectsForClient($this->view->client->id);
        }
    }
    
    /**
     * Override the save action so that we can be more forceful about how issues 
     * are saved
     *
     */
    public function saveAction()
    {
        $this->_setParam('updated', date('Y-m-d H:i:s'));
        $model = null;
        try {
            $model = $this->issueService->saveIssue($this->_getAllParams());
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $model = new Issue();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        } catch (Zend_Mail_Transport_Exception $zmte) {
            // don't worry about it
            $this->log->warn("Failed sending notification messages for issue ".$model->id);
        }
        
        $this->onModelSaved($model);
    }
     

    /**
     * When an issue is saved, do what? 
     *
     * @param Issue $model
     */
    protected function onModelSaved($model)
    {
        if ($this->_getParam('_ajax')) {
			// this was posted via an ajax form - we'll simply reload the
			// parent page via javascript
			echo '<p>Please wait... </p><script>$("#grid-issue-list .pReload").click(); $("#issuedialog").simpleDialog("close");</script>';
		} else {
			if ($model->clientid) {
				$this->redirect('client', 'view', array('id'=>$model->clientid, '#issues'));
			} else {
				$this->redirect('issue');
			}
		}
    }

    
    /**
     * Adds a note to this issue
     *
     */
    public function addNoteAction()
    {
        $issue = $this->byId();
        if ($issue) { 
            $note = $this->_getParam('note');
            $title = 'RE Request #'.$issue->id.': '. $this->_getParam('title');

            $note = $this->notificationService->addNoteTo($issue, $note, $title);

            // If this is a 'new' note, then lets update it to be open now that there's a thing
			if ($issue->status == 'New') {
				$issue->status = 'Open';
			}
			// Save the issue so it's mod time is updated
            $this->issueService->saveIssue($issue);
            $this->notificationService->sendWatchNotifications($note, array('controller' => 'issue', 'action' => 'edit', 'params'=>array('id'=>$issue->id))); 
        }
 
        $this->redirect('issue', 'edit', array('id'=>$issue->id, '#notes'));
    }
    
    
    /**
     * Get the items that need to appear in the project listing
     */
    public function projectListAction()
    {
        $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        $where = array('projectid='=>$project->id);
        $this->bindIssueListViewData($where);

        $this->view->model = $project; 
        $this->view->attachedToType = 'projectid';

        $this->view->minimal = true; 

        $this->renderRawView('issue/list.php'); //ajax-issue-list.php');
    }
    
    /**
     * Get the items needed for the client page listing
     *
     */
    public function clientListAction()
    {
        $client = $this->clientService->getClient((int) $this->_getParam('clientid'));
        
        $where = array('issue.clientid='=>$client->id);
        
        $this->bindIssueListViewData($where);
        
        $this->view->model = $client; 
        $this->view->attachedToType = 'clientid';
        
        $this->view->minimal = true; 

        $this->renderRawView('issue/list.php'); //ajax-issue-list.php');
    }
    
    /**
     * Binds view data based on the where clause for generating the list of
     * issues
     *
     * @param array $where
     */
    private function bindIssueListViewData($where=array())
    {
        $this->view->issues = $this->getIssueList($where);
        $this->view->type = 'list';
    }

	/**
	 * Generates the appropriate query for returning a list of issues
	 *
	 * @param array $where
	 * @return arrayobject
	 */
	protected function getIssueList($where=array())
	{
		$sortDir = $this->_getParam('sortorder', $this->_getParam('dir', 'desc'));

        if ($sortDir == 'up' || $sortDir == 'asc') {
            $sortDir = 'asc';
            $issueParams = array('dir' => 'up');
        } else {
            $sortDir = 'desc';
            $issueParams = array('dir' => 'down');
        }

        $mineOnly = $this->_getParam('mineOnly');
        if ($mineOnly) {
            $where['issue.userid='] = za()->getUser()->getUsername();
            $issueParams['mineOnly'] = $mineOnly;
        }

		$query = $this->_getParam('query');
		if (mb_strlen($query) >= 2) {
			$where[] = new Zend_Db_Expr("issue.title like ".$this->issueService->dbService->quote('%'.$query.'%')." OR issue.description like ".$this->issueService->dbService->quote('%'.$query.'%'));
		}

        $filter = $this->_getParam('titletext');
    	if (mb_strlen($filter) >= 2) {
        	// add some filtering to the query
			$where['issue.title like '] = '%'.$filter.'%';
			$issueParams['titletext'] = $filter;
        }

        $filter = $this->_getParam('severity');
    	if (mb_strlen($filter)) {
        	// add some filtering to the query
			$where['issue.severity = '] = $filter;
			$issueParams['severity'] = $filter;
        }

    	$filter = $this->_getParam('status');
        if ($filter !== null && !is_array($filter) && strlen($filter)) {
            $filter = array($filter);
            $issueParams['status'] = $filter;
        }

    	if (is_array($filter)) {
    		$where['status'] = $filter;
    	    $issueParams['status'] = $filter;
        }

    	$filter = $this->_getParam('type');
    	if (mb_strlen($filter)) {
        	// add some filtering to the query
			$where['issue.issuetype = '] = $filter;
			$issueParams['type'] = $filter;
        }

        $filter = $this->_getParam('clientid');
    	if (mb_strlen($filter)) {
        	// add some filtering to the query
			$where['issue.clientid = '] = $filter;
			$issueParams['clientid'] = $filter;
        }

		$filter = $this->_getParam('projectid');
    	if (mb_strlen($filter)) {
        	// add some filtering to the query
			$where['issue.projectid = '] = $filter;
			$issueParams['projectid'] = $filter;
        }

        $filter = $this->_getParam('startdate');
        if (mb_strlen($filter)) {
        	$where['issue.updated >= '] = date('Y-m-d 00:00:00', strtotime($filter));
        	$issueParams['startdate'] = $filter;
        }

    	$filter = $this->_getParam('enddate');
        if (mb_strlen($filter)) {
        	$where['issue.updated <= '] = date('Y-m-d 23:59:59', strtotime($filter));
        	$issueParams['enddate'] = $filter;
        }
        // If not a User, can only see non-private issues
        if (!za()->getUser()->hasRole(User::ROLE_USER)) {
        	$where['issue.isprivate='] = 0;
        }

        $sort = $this->_getParam('sortname', $this->_getParam('sort', 'updated'));
        $this->view->sort = $sort;
        $issueParams['sort'] = $sort;
        $this->view->sortDir = $sortDir;

        $tmp = new Issue();
        $this->view->severities = $tmp->constraints['severity']->getValues();
        $this->view->types = $tmp->constraints['issuetype']->getValues();
        $this->view->statuses = $tmp->constraints['status']->getValues();

        $sort .= ' '.$sortDir;
        $totalCount = $this->issueService->getIssueCount($where);
        $this->view->pagerName = 'page';
        $currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
        $this->view->clients = $this->clientService->getClients();
        $this->view->totalCount = $totalCount;
        $this->view->listSize = $this->_getParam('rp', za()->getConfig('project_list_size', 10));

        if ($this->_getParam("unlimited")) {
        	$currentPage = null;
        }

		$this->view->searchParams = $issueParams;
		return $this->issueService->getIssues($where, $sort, $currentPage, $this->view->listSize);
	}

    /**
     * Get a list of the new issues
     *
     */
    public function issueListAction()
    {
        $from = za()->getUser()->getLastLogin();
        $type = $this->_getParam('type');
        $date = $type == 'new' ? 'issue.created' : 'issue.updated';
        $this->view->issues = $this->issueService->getIssues(array($date.' > '=> $from), "$date desc", 1, 10);
        $this->view->type = $type;
        $this->renderRawView('issue/ajax-issue-list.php');
    }
    
    /**
     * Links an issue to a particular feature
     */
    public function linkFeatureAction()
    {
        $issue = $this->byId();
        $feature = $this->byId($this->_getParam('featureid'), 'Feature');
        $linkType = $this->_getParam('linktype'); 

        if ($issue == null) {
            $this->flash('Invalid Issue specified');
            $this->redirect('issue', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        
        if ($issue == null || $feature == null) {
            $this->flash('Invalid Feature specified');
            $this->redirect('issue', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        try {
	        // okay, link the feature from the issue
            if ($linkType == 'to') {
                $this->itemLinkService->parentChildLink($issue, $feature);
                $this->flash("Linked issue '$issue->title' to feature '$feature->title'");
            } else {
                $this->itemLinkService->parentChildLink($feature, $issue);
    	        $this->flash("Linked feature '$feature->title' to issue '$issue->title'");
            }
        } catch (Exception $e) {
            $this->flash("Failed linking items: ".$e->getMessage());
        }
        $this->redirect('issue', 'edit', array('id' => $this->_getParam('id'), '#features'));
    }
    
    /**
     * Delete a link between an issue and a feature
     */
    public function removeFeatureAction()
    {
        $issue = $this->byId();
        $feature = $this->byId($this->_getParam('featureid'), 'Feature');
        $linkType = $this->_getParam('linktype'); 
        
        if ($issue == null) {
            $this->flash('Invalid Issue specified');
            $this->redirect('issue', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        
        if ($issue == null || $feature == null) {
            $this->flash('Invalid Feature specified');
            $this->redirect('issue', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        try {
            if ($linkType == 'to') {
       	        // okay, delete the link from the feature TO the issue
    	        $this->itemLinkService->deleteLinkBetween($feature, $issue);
    	        $this->flash("Removed link between feature $feature->title and issue $issue->title");
            } else {
                $this->itemLinkService->deleteLinkBetween($issue, $feature);
                $this->flash("Removed link between issue $issue->title and feature $feature->title");
            }

        } catch (Exception $e) {
            $this->flash("Failed removing link between items: ".$e->getMessage());
        }
        $this->redirect('issue', 'edit', array('id' => $this->_getParam('id'), '#features'));
    }
}

class External_IssueController extends IssueController
{
    public function preDispatch()
    {
        $contact = $this->clientService->getUserContact(za()->getUser());
        // Set the client and stuff also 
        if (!$contact) {
            $this->requireLogin();
            return;
        }

        $this->_setParam('clientid', $contact->clientid);

        // make sure that the user is doing something they're allowed to do
        $id = $this->_getParam('id');
        if ($id) {
            $obj = $this->byId();

            if ($obj->clientid != $contact->clientid) {
            	$this->log->warn("Passed in client id ".$obj->clientid." but user is member of client ".$contact->clientid);
                $this->requireLogin();
            }
        }
        
        $pid = $this->_getParam('projectid');
        if ($pid) {
        	$project = $this->byId($pid, 'Project');
        	if (!$project || $project->clientid != $contact->clientid) {
        		$this->requireLogin();
        	}
        }
        
    }
}
?>