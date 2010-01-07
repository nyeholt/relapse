<?php

include_once 'model/Client.php';

class External_ClientController extends NovemberController 
{
    /**
     * Client Service
     *
     * @var ClientService
     */
    public $clientService;
    
    /**
     * The project service
     *
     * @var ProjectService
     */
    public $projectService;
    
    public function preDispatch()
    {
        $userClient = $this->clientService->getUserClient(za()->getUser());

        if ($userClient != null) {
            $id = $this->_getParam('id');
	        // get the user's client 
	        
	        if ($id != $userClient->id) {
	            $this->_setParam('id', $userClient->id);
	        }
        } else {
            $this->requireLogin();
        }
    }

    /**
     * Give an a-z listing of clients
     */
    public function indexAction()
    {
        $this->viewAction();
    }
    
    /**
     * View a single client
     *
     */
    public function viewAction()
    {
        $this->view->client = $this->byId();
        $this->view->title = $this->view->client->title;
        $this->renderView('client/external-view.php');
    }
        
    /**
     * Override the edit action to supply some selectable relationships
     *
     * @param Bindable $model
     */
    public function editAction($model=null)
    {
        $this->view->relationships = array("Lead", "Opportunity", "Customer", "Dead", "Supplier");
        parent::editAction($model);
    }
    
    /**
     * Redirect to view the client
     *
     * @param Client $model
     */
    protected function onModelSaved($model)
    {
        // go to its view page
        $this->redirect('client', 'view', array('id'=>$model->id));
    }
    
    /**
     * Delete a client
     *
     */
    public function deleteAction()
    {
        return;
    }
}
?>