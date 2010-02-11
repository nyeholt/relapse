<?php

class ClientService
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * TrackerService
     *
     * @var TrackerService
     */
    public $trackerService;
    
    /**
     * ProjectService
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * @var UserService
     */
    public $userService;
    
    /**
     * Get the client that this application is centered around
     * 
     */
    public function getApplicationClient()
    {
    	return $this->getClient(za()->getConfig('owning_company'));
    }
    
    /**
     * Get a client object by its id
     *
     * @param Client $id
     */
    public function getClient($id)
    {
        return $this->dbService->getById((int) $id, 'Client');
    }
    
    /**
     * Get the client that a user is attached to, if any
     */
    public function getUserClient($user)
    {
        $contact = $this->getContact($user->contactid);
        if ($contact) {
            return $this->getClient($contact->clientid);
        }
    }

    /**
     * Get a client object by its id
     *
     * @param Client $id
     * @return Client
     */
    public function getClientByField($fields)
    {
        return $this->dbService->getByField($fields, 'Client');
    }
    
    /**
     * Get a list of clients
     *
     * @param array $where
     * @param array $user
     * @return ArrayObject
     */
    public function getClients($where = array(), $order='title asc', $page=null, $number=null)
    {
        $select = $this->dbService->select();
		$select->from('client', '*');
		
		/*if ($user != null) {
			$table = strtolower(get_class($user));
			$select->joinLeft($table, 'client.ownerid = '.$table.'.id', '');
			$select->where($table.'.id = ?', $user->getId());
		}*/

		foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}

		$select->where('deleted=?', 0);
		$select->order($order);

		if (!is_null($page)) {
		    $select->limitPage($page, $number);
		}
		
		$clients = $this->dbService->fetchObjects('Client', $select);

		return $clients;
    }
    
    /**
     * Gets the list of letters that client names begin with.
     * 
     * Useful for UI related stuff, maybe other things too? 
     */
    public function getClientTitleLetters($relationship = 'Customer')
    {
    	$result = null;
        /* @var $select Zend_Db_Select */
        if ($relationship == "ALL") {
	        $query = "SELECT DISTINCT UPPER(LEFT(title,1)) as letter FROM client ORDER BY letter";
	        $result = $this->dbService->query($query);
        } else {
	        $query = "SELECT DISTINCT UPPER(LEFT(title,1)) as letter FROM client WHERE relationship = ? ORDER BY letter";
	        $result = $this->dbService->query($query, array($relationship));
        }
        
        
        $letters = array();
        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
	        $letters[] = $row['letter'];
	    }

	    return $letters;
    }
    
    /**
     * Get the total number of clients for a given where clause
     *
     * @param array $where
     * @return int
     */
    public function getClientCount($where)
    {
        $select = $this->dbService->select();
		$select->from('client', new Zend_Db_Expr('count(*) as total'));
		
		foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}

		$select->where('deleted=?', 0);

		$count = $this->dbService->fetchOne($select);

		return $count;
    }
    
    /**
     * Delete a client (flag as deleted anyway)
     *
     * @param Client $client
     */
    public function deleteClient(Client $client)
    {
        $client->deleted = true;
        if ($this->dbService->saveObject($client)) {
            $this->trackerService->track('delete-client', $client->id);
            return true;
        }
        return false;
    }
    
    /**
     * Get a client object by its id
     *
     * @param Contact $id
     */
    public function getContact($id)
    {
        return $this->dbService->getById($id, 'Contact');
    }
    
    /**
     * Get a client object by its id
     *
     * @param string $field
     * @return Contact
     */
    public function getContactByField($field, $value)
    {
        return $this->getContactByFields(array($field=>$value));
    }
    
    /**
     * Get a client object by some fields
     *
     * @param string $field
     * @param string $value
     * @return Contact
     */
    public function getContactByFields($fields)
    {
        return $this->dbService->getByField($fields, 'Contact');
    }
    
    public function getContactsByNames($names)
    {
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
        $select->from('contact');

        $where = '';
        $sep = '';
        foreach ($names as $name) {
            if (mb_strlen($name) < 3) {
                continue; 
            }
            $select->orWhere("firstname like ".$this->dbService->quote($name.'%'));
            $select->orWhere("lastname like ".$this->dbService->quote($name.'%'));
/*            $where .= $sep . ' firstname like '.$this->dbService->quote($name.'%'); 
            $sep = ' OR ';
            $where .= $sep . ' lastname like  '.$this->dbService->quote($name.'%');*/
        }
        
        $contacts = $this->dbService->fetchObjects('Contact', $select);
        $select->order("firstname asc");

		return $contacts;
        
    }
    
    /**
     * Get all the first letters of contact names
     */
    public function getContactLetters()
    {
    	$query = "SELECT DISTINCT UPPER(LEFT(firstname,1)) as letter FROM contact ORDER BY letter";
        
        $result = $this->dbService->query($query);

        $letters = array();
        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
	        $letters[] = $row['letter'];
	    }

	    return $letters;
    }

    /**
     * Get contacts for a given client
     *
     * @param Client $client
     */
    public function getContacts($client=null, $where=array(), $order='firstname asc', $page=null, $number=null)
    {
		if ($client) {
            $where['clientid='] = $client->id;
		}

		$select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from('contact', '*');
		$select->joinLeft('client', 'contact.clientid=client.id', 'client.title as company');

		foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}
		
		if (!is_null($page)) {
		    $select->limitPage($page, $number);
		}

		$select->order($order);

		$contacts = $this->dbService->fetchObjects('Contact', $select);

		return $contacts;
    }
    
    /**
     * Get the number of contacts in the system from the given where clause
     */
    public function getContactCount($where)
    {
        return $this->dbService->getObjectCount($where, 'contact');
    }
    
    /**
     * Save a client. If the params['id'] value is set, it will update
     * that client object instead of creating a new one.
     *
     * @param array $params
     * @return Client
     */
    public function saveClient($params)
    {
		$id = is_array($params) ? ifset($params, 'id', 0) : $params->id;
    	$this->trackerService->track('save-client', $id);
        return $this->dbService->saveObject($params, 'Client');
    }
    
    /**
     * Creates a user object for the given contact
     * 
     * Default attempted username is firstname.surname
     * 
     * Failing that, initial.surname
     */
    public function createUserForContact(Contact $contact)
    {
                
        // try and find a user for the given username
        $username = $contact->firstname . '.' . $contact->lastname;
        $username = trim($username, '.');
        
        $username = mb_strtolower(preg_replace('/[^a-zA-Z.+_-]/', '', $username));
        if ($username == '.') {
            throw new ExistingUserException("Contact must have a firstname and surname");
        }

        if ($this->userService->getByName($username)) {
            // try a different username
            $username = $username{0}.$contact->lastname;
            $username = trim($username, '.');
            $username = mb_strtolower(preg_replace('/[^a-zA-Z.+_-]/', '', $username));
            if ($this->userService->getByName($username)) {
                // crapp it
                throw new Exception("All possible usernames taken");
            }
        }
        
        try {
	        $this->dbService->beginTransaction();
	        
	        $new_pass = substr(md5(uniqid(rand(),1)), 3, 5);
	        
	        $params = array(
	            'username' => $username, 
	            'email' => $contact->email, 
	            'firstname' => $contact->firstname,
	            'lastname' => $contact->lastname,
	            'contactid' => $contact->id,
	            'password' => $new_pass,
	            );

	        $user = $this->userService->createUser($params, false, User::ROLE_EXTERNAL);
	        $this->trackerService->track('create-contact-user', $contact->id);
	        $this->dbService->commit();
        } catch (Exception $e) {
        	$this->log->debug($e->getMessage().": \r\n". $e->getTraceAsString());
            $this->dbService->rollback();
            throw $e;
        }

        return $user;
    }
    
/**
     * Get the client that a user is attached to, if any
     */
    public function getUserContact($user)
    {
        return $this->getContact($user->contactid);
    }
    
    /**
     * Get the support project for this client 
     *
     * @param Client $client
     * @return Project
     */
    public function getClientSupportProject(Client $client)
    {
        $project = $this->projectService->getProjectByField('title', $client->title.' Support');
        if (!$project) {
            $params = array();
            $params['title'] = $client->title.' Support';
            $params['clientid'] = $client->id;
            $project = $this->projectService->saveProject($params);
        }
        /* @var $project Project */
        if ($project->deleted) {
            $project->deleted = false;
            $this->projectService->saveProject($project);
        }

        return $project;
    }
    
    /**
     * Save a contact against a given client object
     * 
     *
     * @param array|Contact $params 
     * @return Contact
     */
    public function saveContact($params)
    {
        $contact = null;
        try {
            $this->dbService->beginTransaction();
                // Get an existing one 
	        $contact = $this->dbService->saveObject($params, 'Contact');
	        
	        // check for a user object to also update
	        if ($contact->id) {
	            $user = $this->userService->getUserByField('contactid', $contact->id);
	            if ($user) {
	                $params = array(
	                    'firstname' => $contact->firstname,
	                    'lastname' => $contact->lastname,
	                    'email' => $contact->email,
	                );

                    $this->userService->updateUser($user, $params);
	            }

	        }
	        $this->trackerService->track('update-contact', $contact->id, null, null, print_r($params, true));
	        $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            $this->log->err("Failed creating contact for params ".print_r($params, true).": ".$e->getMessage());
            $contact = null;
            throw $e;
        }

        return $contact;
    }
    
    /**
     * Import contacts from a file
     * 
     * @param string $fname the filename to import from
     */
    public function importContacts($fname)
    {
        $fname = $_FILES['import']['tmp_name'];
        $handle = fopen($fname, "r");
        $fields = fgetcsv($handle, 1024, ",");
        $detail = array();
        while($data = fgetcsv($handle, 1000, ",")) {
            $detail[] = $data;
        }

        $x = 0;
        $y = 0;

        $lines = array();
        foreach($detail as $i) {
            foreach($fields as $z) {
                $lines[$x][$z] = $i[$y];
                $y++;
            }
            $y = 0;
            $x++;
        }
        $errors = array();
        $contacts = new ArrayObject();
        
        
        $dbService = $this->dbService;
        $dbService->beginTransaction();
        foreach ($lines as $line) {
            // check for a client that this user has attached to them
            // if the client doesn't exist, create one
            $clientName = trim(ifset($line, 'Organization'));
            if (!$clientName) {
                $clientName = trim(ifset($line, 'Organisation'));
            }
            if (!$clientName) {
                $clientName = "[Unassigned]";
            }

            $params = array('title'=>$clientName);
            
            $existing = $this->getClientByField($params);
            if (!$existing) {
                $params['website'] = trim(ifset($line, 'Web Page 1'));
                $params['relationship'] = trim(ifset($line, 'Relationship'));

	            try {
	                $existing = $this->saveClient($params);
	            } catch (InvalidModelException $ime) {
	                throw new Exception("Failed creating client for ".print_r($ime, true));
	            }
            } else {
                $web = trim(ifset($line, 'Web Page 1'));
                $rel = trim(ifset($line, 'Relationship'));
                $save = false;
                if (mb_strlen($web)) {
                    // save the website
                    $existing->website = $web;
                    $save = true;
                }
                if (mb_strlen($rel)) {
                    $existing->relationship = $rel;
                    $save = true;
                }
                
                if ($save) {
                    $this->saveClient($existing);
                }
            }
            
            $params = array(
                'firstname' => trim(ifset($line, 'First Name')),
                'lastname' => trim(ifset($line, 'Last Name')),
                'email' => trim(ifset($line, 'Primary Email')),
                'altemail' => trim(ifset($line, 'Secondary Email')),
                'department' => trim(ifset($line, 'Department')),
                'directline' => trim(ifset($line, 'Work Phone')),
                'mobile' => trim(ifset($line, 'Mobile Number')),
                'fax' => trim(ifset($line, 'Fax Number')),
                'title' => trim(ifset($line, 'Job Title')),
                'businessaddress' => trim(ifset($line, 'Work Address')).' '.
                    trim(ifset($line, 'Work Address 2'))."\n".
                    trim(ifset($line, 'Work City'))."\n".
                    trim(ifset($line, 'Work State')).' '.
                    trim(ifset($line, 'Work ZipCode'))."\n".
                    trim(ifset($line, 'Work Country')),
                
                'clientid' => $existing->id,
            );

            try {
                $contact = $this->saveContact($params);
                $this->log->debug("Created contact ".$contact->email);
                $contacts[] = $contact;
            } catch (InvalidModelException $ime) {
                $errors = $errors + $ime->getMessages();
            }
        }
        
        $dbService->commit();

        if (count($errors)) {
            throw new ContactImportException($errors, $contacts);
        }
        return $contacts;
    }
}

class ContactImportException extends Exception
{
    public $errors;
    public $imported; 
    
    public function __construct($errors, $imported)
    {
        parent::__construct('Failed importing users');
        $this->errors = $errors;
        $this->imported = $imported;
    }
}
?>