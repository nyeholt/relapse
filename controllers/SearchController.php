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

	/**
	 *
	 * @var DbService
	 */
	public $dbService;

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


	/**
	 * Get a list of items suitable for a flexigrid display
	 */
	public function listAction()
	{
		$type = $this->_getParam('type');
		$items = $this->getList($type);

		$dummy = new $type;
		$listFields = $dummy->listFields();

		$asArr = array();
		$aggrRow = array();
		foreach ($items as $item) {
			$cell = array();
			foreach ($listFields as $name => $display) {
				if (method_exists($item, $name)) {
					$cell[] = $item->$name();
				} else {
					$cell[] = $item->$name;
				}
			}

			$row = array(
				'id' => $item->id,
				'cell' => $cell,
			);

			$asArr[] = $row;
		}
		$obj = new stdClass();
		$obj->page = ifset($this->_getAllParams(), $this->view->pagerName, 1);
		$obj->total = $this->view->totalCount;
		$obj->rows = $asArr;
		$this->getResponse()->setHeader('Content-type', 'text/x-json');
		$json = Zend_Json::encode($obj);
		echo $json;
	}

	/**
	 * Generates the appropriate query for returning a list of issues
	 * 
	 * @param array $where
	 * @return arrayobject
	 */
	protected function getList($type, $where=array())
	{
		$query = $this->_getParam('query');
		if (mb_strlen($query) >= 2) {
			$where[] = new Zend_Db_Expr("title like ".$this->dbService->quote('%'.$query.'%')." OR description like ".$this->dbService->quote('%'.$query.'%'));
		}

		// Handle this up here otherwise a model object might take
		$sortDir = $this->_getParam('sortorder', $this->_getParam('dir', 'desc'));
        if ($sortDir == 'up' || $sortDir == 'asc') {
            $sortDir = 'asc';
        } else {
            $sortDir = 'desc';
        }

		// now just iterate parameters
		$params = $this->_getAllParams();
		unset($params['title']);
		unset($params['sortorder']);
		$dummyObj = new $type;
		// get all the type's parameters
		$fields = $dummyObj->unBind();
		foreach ($fields as $name => $val) {
			// if we have a param with $name, add it to the filter
			$val = ifset($params, $name, null);
			if ($val) {
				$where[$name.' ='] = $val;
			}
		}

        // If not a User, can only see non-private issues
        if (za()->getUser()->getRole() == User::ROLE_EXTERNAL) {
			if (isset($fields['isprivate'])) {
				$where['isprivate='] = 0;
			}

			if (isset($fields['clientid'])) {
				$client = $this->clientService->getUserClient(za()->getUser());
				$where['clientid='] = $client->id;
			}
		}

		$sort = $this->_getParam('sortname', $this->_getParam('sort', 'updated'));
        $sort .= ' '.$sortDir;
		
        $this->view->totalCount = $this->dbService->getObjectCount($where, $type);
        $this->view->pagerName = 'page';
        $currentPage = ifset($params, $this->view->pagerName, 1);
        $this->view->listSize = $this->_getParam('rp', za()->getConfig('project_list_size', 10));
        if ($this->_getParam("unlimited")) {
        	$currentPage = null;
        }

		return $this->dbService->getObjects($type, $where, $sort, $currentPage, $this->view->listSize);
	}
}
?>