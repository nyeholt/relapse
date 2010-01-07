<?php
include_once 'extensions/performance/model/Performancereview.php';

class PerformanceReviewController extends NovemberController 
{
    /**
     * UserService
     *
     * @var UserService
     */
    public $userService;
    
    /**
     * @var PerformanceReviewService
     */
    public $performanceReviewService;
    
    private function validUser($username)
    {
        if ($username == null) {
            $username = za()->getUser()->getUsername();
        }
        
        $user = $this->userService->getByName($username);
        if ($user == null) {
            throw new Exception("Invalid user");
        }
        
        // if current user is not an admin, die
        $currentUser = za()->getUser();
        if ($currentUser->id != $user->id) {
            // check to see if it's an admin user
            if (!$currentUser->hasRole(User::ROLE_POWER)) {
                // bad bad
                throw new Exception("Cannot view this user");
            }
        }

        return $user;
    }
    
    public function indexAction()
    {
        $this->listAction();
    }
    
    /**
     * List all the performance review forms for a given user
     *
     */
    public function listAction()
    {
        $user = null;
        try {
            $user = $this->validUser($this->_getParam('username'));
        } catch (Exception $e) {
            // just show an error?
            return;
        }

        if ($user == null) {
            return;
        }

        // get all the performance reviews for this user
        $this->view->items = $this->dbService->getObjects('PerformanceReview', array('username=' => $user->username, 'nextversionid='=>0), 'created desc');

        $this->view->user = $user;
        $this->renderView('performancereview/list.php');

    }
    
    public function editAction($model=null)
    {
        $user = $this->validUser($this->_getParam('username'));
        if ($user == null) {
            return;
        }
        $this->view->user = $user;

        parent::editAction($model);
    }
    
    public function viewAction()
    {
        $user = null;
        try {
            $user = $this->validUser($this->_getParam('username'));
        } catch (Exception $e) {
            // just show an error?
            return;
        }

        if ($user == null) {
            return;
        }

        $this->view->model = $this->byId();
        $this->view->user = $this->userService->getByName($this->view->model->username);
        $this->prepareForView($this->view->model);
        $this->renderRawView('performancereview/view.php');
    }
    
    protected function prepareForEdit($model)
    {
        if ($model->nextversionid != 0) {
            throw new Exception("Cannot edit a previous version");
        }
        $this->view->users = $this->userService->getUserList();
        $this->view->versions = $this->dbService->getObjects('PerformanceReview', array('originalversion=' => $model->originalversion, 'nextversionid<>'=>0), 'created desc');
    }
    
    public function saveAction()
    {
        $user = $this->validUser($this->_getParam('username'));
        if ($user == null) {
            return;
        }
        $this->view->user = $user;

        $review = $this->byId();

        if ($review == null) {
            $review = new PerformanceReview();
        }
        
        try {
            $params = $this->filterParams($this->_getAllParams());
            $model = $this->performanceReviewService->saveReview($review, $params);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $model = new $modelType();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        }
        $this->onModelSaved($model);
    }
    
    /**
     * Called to redirect after saving a model object
     *
     */
    protected function onModelSaved($model=null)
    {
        $user = $this->userService->getByName($model->username);
        if ($user == null) {
            $this->redirect($this->_request->getControllerName());
        }
        $this->redirect('user', 'edit', array('id'=>$user->id, '#reviews'));
    }
    
    public function deleteAction()
    {
        $user = $this->validUser($this->_getParam('username'));
        if ($user == null) {
            return;
        }
        $this->view->user = $user;
        parent::deleteAction();
    }
}
?>