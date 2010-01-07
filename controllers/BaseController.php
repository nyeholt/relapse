<?php

class BaseController extends NovemberController 
{
    
    /**
     * Before rendering, we'll add some extra stuff into the view!
     *
     */
    protected function renderView($script)
    {
        if (za()->getUser()->hasRole(User::ROLE_USER)) {
	        $this->view->actionList = new CompositeView('layouts/actions-list.php');
	        
	        // Let's get a bunch of the current user's oldest incomplete
	        // tasks to put in the list.
	        
	        $projectService = za()->getService('ProjectService');
	        /* @var $projectService ProjectService */
	        
	        $this->view->actionList->tasks = $projectService->getUserTasks(za()->getUser(), array('complete='=>0));
        }

        parent::renderView($script);
    }
}