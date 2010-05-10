<?php
include_once 'model/PanelFavourite.php';

class IndexController extends BaseController 
{
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
	 *
	 * @var DbService
	 */
	public $dbService;
    
    public $allowedMethods = array('indexAction' => 'get', 'favouritepaneAction' => 'post', 'deletefavouriteAction' => 'post');
    
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
    	
//    	$this->view->taskInfo = $this->projectService->getTimesheetReport($user, null, null, -1, $start, $end, $cats, $order);
//    	$this->view->dayTasks = $this->projectService->getDetailedTimesheet($user, null, null, null, -1, $startDay, $endDay);

		$this->view->latest = $this->projectService->getProjects(array('ismilestone=' => 0), 'updated desc', 1, 10);

    	$task = new Task();
    	$this->view->categories = $task->constraints['category']->getValues();
    	$this->view->startDate = $start;
        $this->view->endDate = $end;

		$this->view->favourites = $this->dbService->getObjects('PanelFavourite', array('creator=' => za()->getUser()->username));

        $this->renderView('index/index.php');
    }

    public function bookmarkAction()
    {
        $feed = $this->deliciousService->getFeed(self::FEED_URL);
        
        $this->view->feed = $feed;
    	
        $this->renderView('index/bookmarks.php');
    }

	public function favouritepaneAction() {
		$favourite = new PanelFavourite();
		$favourite->bind($this->_getAllParams());

		$out = $this->dbService->saveObject($favourite);
		echo $this->ajaxResponse($out);
	}

	public function deletefavouriteAction() {
		$fav = $this->byId(null, 'PanelFavourite');
		if ($fav) {
			$this->dbService->delete($fav);
		}
		echo $this->ajaxResponse($fav->id);
	}
}
?>