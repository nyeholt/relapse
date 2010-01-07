<?php

class CodereviewController extends NovemberController 
{
	/**
	 * ProjectService
	 *
	 * @var ProjectService
	 */
	public $projectService;
	
	/**
	 * @var NotificationService
	 */
	public $notificationService;
	
    public function prepareForEdit($model)
    {
    	$this->view->projects = $this->projectService->getProjects();
    	// get the project
		if ($model->projectid) {
			$this->view->project = $this->projectService->getProject($model->projectid);
		} else {
			$this->view->project = $this->projectService->getProject($this->_getParam('projectid'));
		}
		
		$this->view->statuses = $model->constraints['status']->getValues();
		        
		if ($this->view->project == null) {
			throw new Exception("Invalid project specified");
		}
    }
    
    public function prepareForView($model)
    {
        $this->view->existingWatch = $this->notificationService->getWatch(za()->getUser(), $model->id, 'Codereview');
        // load all its notes
        $comments = $this->notificationService->getNotesFor($model);
        $comms = array();
        foreach ($comments as $note) {
            $current = ifset($comms, $note->title, array());
            $current[] = $note;
            $comms[$note->title] = $current;
        }
        $this->view->comments = $comms;
    }
    
    public function onModelSaved($model)
    {
    	$this->redirect('codereview', 'view', array('id'=>$model->id));
    }
    
    public function onModelDeleted($model)
    {
        $this->flash('Deleted review #'.$model->id);
        $this->redirect('project', 'view', array('id'=>$model->id, '#codereviews'));
    }

    public function addCommentAction()
    {
        $review = $this->byId();
        
        if (!$review) {
            throw new Exception("Invalid code review");
        }

        // Creating a new note and adding it on
        $note = $this->notificationService->addNoteTo($review, $this->_getParam('comment'), $this->_getParam('line')); 
        $this->notificationService->sendWatchNotifications($note, array('controller' => 'codereview', 'action' => 'view', 'params'=>array('id'=>$review->id))); 
        $this->redirect('codereview', 'view', array('id'=>$review->id));

    }
    
    public function listProjectAction()
    {
    	$project = $this->projectService->getProject($this->_getParam('projectid'));
    	$this->view->items = $this->dbService->getObjects('Codereview', array('projectid=' => $project->id), 'created desc');
    	$this->renderRawView('codereview/list.php');
    }
}
?>