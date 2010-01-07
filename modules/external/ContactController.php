<?php

include_once 'model/Contact.php';

class External_ContactController extends NovemberController 
{
    /**
     * Client Service
     *
     * @var ClientService
     */
    public $clientService;
    
    /**
     * @var UserService
     */
    public $userService;
    
    public function preDispatch()
    {
        // make sure the id is valid
        $id = $this->_getParam('id');
        if ($id && $id != za()->getUser()->contactid) {
            // see whether the id belongs to the same company at least
            $contact = $this->byId(); 
            $userContact = $this->clientService->getUserContact(za()->getUser());
            if ($contact->clientid != $userContact->clientid) {
                $this->requireLogin();
            }
        }
    }

    public function indexAction()
    {
        // get all the contacts for the currently logged in user's company.
        $contact = $this->clientService->getContact(za()->getUser()->contactid);
        
        if (!$contact) {
            return;
        }
        
        $client = $this->clientService->getClient($contact->clientid);
        
        $totalCount = $this->clientService->getContactCount(array('clientid='=>$client->id));

        $this->view->pagerName = 'pager';
        $currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
        
        $this->view->totalCount = $totalCount;
        $this->view->listSize = za()->getConfig('project_list_size');

        $this->view->contacts = $this->clientService->getContacts($client, array(), 'firstname asc', $currentPage, $this->view->listSize);
        
        $this->renderView('contact/index.php');
    }
    
    /**
     * Load the contacts for a given client id
     */
    public function contactListAction()
    {
        $client = $this->clientService->getUserClient(za()->getUser());
        if (!$client) {
            echo "Failed loading contacts";
            return;
        }

        $this->view->client = $client;
        $this->view->contacts = $this->clientService->getContacts($client);
        
        $this->renderRawView('contact/ajax-list.php');
    }

    /**
     * Prepare a contact for being edited. 
     */
    public function prepareForEdit($model)
    {
        if ($model == null) {
            $this->error("Specified contact not found");
            return;
        }
        // check the existence of the client to add this contact to
        $cid = $model->clientid ? $model->clientid : (int) $this->_getParam('clientid');
        $client = $this->clientService->getClient($cid);
        
        if ($client == null) {
            $this->error("Specified contact not found");
            return;
        }

        $this->view->title = "Editing Contact";
        $this->view->client = $client;
        $this->view->clients = $this->clientService->getClients();
        $this->view->assocUser = $this->userService->getUserbyField('contactid', $model->id);
    }
    
    /**
     * When saving, use the clientController saveContact so we can update
     * the user's information too.
     */
    protected function saveObject($params, $modelType)
    {
        return $this->clientService->saveContact($params);
    }
    
    /**
     * When the model is saved, 
     * redirect to view the client, not the contact
     */
    public function onModelSaved($model)
    {
        $this->redirect('client', 'view', array('id'=>$model->clientid, '#contacts'));
    }

    public function deleteAction()
    {
        // not doing a delete!
    }
}
?>