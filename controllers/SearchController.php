<?php
class SearchController extends BaseController 
{
    /**
     * The search service
     *
     * @var SearchService
     */
    public $searchService;
	
    /**
     * @var ClientService
     */
	public $clientService;
    
    public function indexAction()
    {
        $query = $this->_getParam('query');
        
		if ($this->_getParam('contacts')) {
			$this->searchContacts($this->_getParam('query'));
			return;
		}
		
        $hits = $this->searchService->search($query);
        $this->view->results = $this->filterResults($hits);
        $this->view->query = $query;
        $this->view->perPage = 6;
        $this->renderView('search/results.php');
    }
	
	public function searchContacts($query)
	{
	    $query = trim($query);
		$this->view->query = $query;
		
		// split into two and look for an exact match
        $names = split(" ", $query);
        if (count($names) == 2) {
            // see if there's someone with the same firstname/surname
            $contact = $this->clientService->getContactByFields(array('firstname' => $names[0], 'lastname'=>$names[1]));
            if ($contact != null) {
                $this->redirect('contact', 'edit', array('id' => $contact->id));
                return;
            }
        }

        // Otherwise, just look for contacts with a firstname or lastname like the entered name
        $this->view->contacts = $this->clientService->getContactsByNames($names);
        
		$this->renderView('search/contacts.php');
	}
    
    private function filterResults($hits)
    {
        $validTypes = array('client' => 1, 'task' => 1, 'project' => 1, 'contact' => 1, 'note' => 1, 'faq' => 1, 'issue' => 1);
        $return = new ArrayObject();
        foreach ($hits as $result)
        {
	        try {
	            $result->__get('identifier');
	        } catch (Zend_Search_Lucene_Exception $zse) {
	            za()->log("Failed to retrieve result ".$zse->getMessage());
	            continue;
	        }
	
	        try {
	            $result->__get('id');
	        } catch (Zend_Search_Lucene_Exception $zse) {
	            za()->log("Failed to retrieve result for ".$result->identifier);
	            continue;
	        }
	        $type = mb_strtolower($result->type);
	        try {
	        		$del = $result->__get('deleted');
		        	if ($del) {
		        		continue;
		        	}
	        } catch (Zend_Search_Lucene_Exception $zse) {
	        	// ignore
	        }

	        if (isset($validTypes[$type])) {
	            $return->append($result);
	        }
        }
        
        return $return;
    }
}
?>