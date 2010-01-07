<?php

include_once 'model/Client.php';

class ClientController extends BaseController 
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
    
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * Give an a-z listing of clients
     */
    public function indexAction()
    {
        $this->view->clientPagerName = 'client-page';
        $this->view->relationship = $this->_getParam('relation', 'Customer');
        $this->view->clientLetters = $this->clientService->getClientTitleLetters($this->view->relationship);

        $currentLetter = ifset($this->_getAllParams(), $this->view->clientPagerName, ifset($this->view->clientLetters, 0, 'A'));
        
        $obj = new Client();
        $this->view->relationships = $obj->constraints['relationship']->getValues();
        
        if ($this->view->relationship == "ALL") {
        	$this->view->clients = $this->clientService->getClients(array('title like '=>$currentLetter.'%'), 'title asc'); // , $currentPage, za()->getConfig('project_list_size'));
        }else{
        	$this->view->clients = $this->clientService->getClients(array('relationship='=>$this->view->relationship, 'title like '=>$currentLetter.'%'), 'title asc'); // , $currentPage, za()->getConfig('project_list_size'));
        }
        $this->renderView('client/list.php');
    }
    
    /**
     * View a single client
     *
     */
    public function viewAction()
    {
        $this->view->client = $this->byId();
        $this->view->title = $this->view->client->title;
        $this->view->existingWatch = $this->notificationService->getWatch(za()->getUser(), $this->view->client);
        $this->renderView('client/view.php');
    }
    

    protected function prepareForEdit($model)
    {
        $this->view->relationships = $this->view->model->constraints['relationship']->getValues();        
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
        $client = $this->byId();
        $this->clientService->deleteClient($client);
        $this->redirect('client');
    }
}
?>