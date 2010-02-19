<?php
class IndexController extends BaseController 
{
    const FEED_URL = 'http://del.icio.us/rss/tag/lmintra';
    
    /**
     * The project service
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var DeliciousService
     */
    public $deliciousService;
    
    public $allowedMethods = array('indexAction' => 'get');
    
    public function indexAction()
    {
    	$user = za()->getUser();
    	// If it's an external user, redirect to the external module
		if ($user->getDefaultModule() != '') {
			// redirect appropriately
			$this->redirect('index', null, null, $user->getDefaultModule());
    	    return;
		}
    	
        $this->view->items = $this->notificationService->getWatchedItems($user, array('Project', 'Client'));

        $cats = array();
        $start = date('Y-m').'-01 00:00:00';
        $end = date('Y-m-t').' 23:59:59';
        $order = 'endtime desc';
        
        $startDay = date('Y-m-d').' 00:00:00';
    	$endDay = date('Y-m-d').' 23:59:59';
    	
    	$this->view->taskInfo = $this->projectService->getTimesheetReport($user, null, null, -1, $start, $end, $cats, $order);
    	$this->view->dayTasks = $this->projectService->getDetailedTimesheet($user, null, null, null, null, $startDay, $endDay);

		$this->view->latest = $this->projectService->getProjects(array('ismilestone=' => 0), 'updated desc', 1, 10);
    	
    	$task = new Task();
    	$this->view->categories = $task->constraints['category']->getValues();
    	$this->view->startDate = $start;
        $this->view->endDate = $end;
        
        $this->renderView('index/index.php');
    }

    public function bookmarkAction()
    {
        $feed = $this->deliciousService->getFeed(self::FEED_URL);
        
        $this->view->feed = $feed;
    	
        $this->renderView('index/bookmarks.php');
    }
}
?>