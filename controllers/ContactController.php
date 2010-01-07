<?php

include_once 'model/Contact.php';

class ContactController extends BaseController 
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
    
    public function indexAction()
    {
    	$this->view->pagerName = "contact-page";
    	$this->view->letters = $this->clientService->getContactLetters();
    	$currentLetter = ifset($this->_getAllParams(), $this->view->pagerName, ifset($this->view->letters, 0, 'A'));

        $this->view->contacts = $this->clientService->getContacts(null, array('firstname like '=>$currentLetter.'%'), 'firstname asc');
        
        $this->renderView('contact/index.php');
    }
    
    public function prepareForEdit($model)
    {
        // check the existence of the client to add this contact to
        $cid = $model->clientid ? $model->clientid : (int) $this->_getParam('clientid');
        $client = $this->clientService->getClient($cid);
        
        if ($client == null) {
            $this->flash("Client not found, please update!");
            $client = new Client();
        }

        $this->view->title = "Editing Contact";
        $this->view->client = $client;
        $this->view->clients = $this->clientService->getClients();
        
        $this->view->assocUser = null;
        if ($model->id) {
            $this->view->assocUser = $this->userService->getUserbyField('contactid', $model->id);
        }
    }

    /**
     * Load the contacts for a given client id
     *
     */
    public function contactListAction()
    {
        $client = $this->clientService->getClient((int) $this->_getParam('clientid'));
        if (!$client) {
            echo "Failed loading contacts";
            return;
        }
        
        $this->view->client = $client;
        $this->view->contacts = $this->clientService->getContacts($client);
        
        $this->renderRawView('contact/ajax-list.php');
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

    /**
     * Create a user for the given contact. 
     */
    public function createUserAction()
    {
        $contact = $this->byId();

        try {
            $this->clientService->createUserForContact($contact);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            za()->log (print_r($ime->getMessages(), true), Zend_Log::ERR);
        }

        $this->redirect('contact', 'edit', array('id' => $contact->id));
    }
    
    /**
	 * Contact importing
	 *
	 */
    public function contactImportAction()
    {
        $this->renderView('contact/import-contacts.php');
    }

    public function uploadContactsAction()
    {
        if (!isset($_FILES['import']) && !isset($_FILES['import']['tmp_name'])) {
            throw new Exception("Import file not found");
        }

        $fname = $_FILES['import']['tmp_name'];
        $contacts = null;
        try {
            $contacts = $this->clientService->importContacts($fname);
        } catch (ContactImportException $cie) {
            $msg = array("Imported ".count($contacts)." contacts, ".count($cie->errors)." not imported.", $cie->errors);
            $this->flash($msg);
            $this->redirect('contact', 'contactimport');
            return;
        }

        $this->flash("Imported ".count($contacts)." contacts");
        $this->redirect('contact');
    }
}
?>