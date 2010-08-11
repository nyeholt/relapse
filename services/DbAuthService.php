<?php

include_once 'november/model/UserRole.php';

/**
 * Manages both authentication and authorization
 *
 */
class DbAuthService extends AuthService implements Configurable 
{
	
	/**
	 * The db service for retrieving objects
	 *
	 * @var DbService
	 */
	public $dbService;

	/**
	 * The tracker service
	 *
	 * @var TrackerService
	 */
	public $trackerService;
	
	/**
	 * The authentication component being used
	 *
	 * @var 
	 */
	public $authComponent;
	
	
	/**
	 * Configure the service
	 *
	 * @param array $config
	 */
	public function configure($config)
	{
	    $this->userClass = ifset($config, 'user_class', 'User');
	}
	
	/**
	 * Given a username and password,
	 * validates the user and returns the 
	 * new object.
	 * @return boolean whether auth was successful
	 */
	public function authenticate($username, $password)
	{
	    $authResult = $this->authComponent->authenticate($username, $password, $this->userClass);
	    $this->log->debug(__CLASS__.':'.__LINE__." Authentication resulted in ".get_class($authResult));
	    if ($authResult instanceof NovemberUser) {
	        $this->setAuthenticatedUser($authResult);
	        return $authResult;
	    } 
        $this->trackerService->track('failed-login', $username);
	    return false;
	}

	
	/**
	 * Sets the passed in user as the authenticated
	 * user of the application. Will generate a ticket
	 * for this user which will be used for subsequent
	 * requests. 
	 *
	 * @param NovemberUser $user
	 */
	public function setAuthenticatedUser(NovemberUser $user, $regenTicket=true)
	{
	    if ($regenTicket) {
		  $user->ticket = $this->generateTicket($user->getUsername());
	    }
        $today = date('Y-m-d H:i:s');
		$user->setLastLogin($today);
		$this->dbService->updateObject($user);
		za()->setUser($user);
	}

	/**
	 * Validates that the given user has the given ticket, 
	 * and sets them into the current user session
	 *
	 * @param string $username
	 * @param string $ticket
	 */
	public function validateTicket($username, $ticket)
	{
		$fields = array('username' => $username, 'ticket' => $ticket);
		$user = $this->dbService->getByField($fields, $this->userClass);

		if ($user != null) {
		    $this->setAuthenticatedUser($user, false);
		}
		
		return $user;
	}
	
	/**
	 * Resets a user's password
	 * @return boolean whether the user was found
	 */
	public function resetPassword($email, $notify = true)
	{
		if (is_string($email)) {
			$fields = array('email' => $email);

			$user = $this->dbService->getByField($fields, $this->userClass);

			if ($user == null) {
				return false;
			}
		} else {
			$user = $email;
			$email = $user->email;
		}



		// store it, then create a URL that will directly auth that ticket, letting them login and
		// change their password
		$user->ticket = $this->generateTicket($email);
		$this->dbService->updateObject($user);
		//exit("Updating user ".print_r($user, true)." to $new_pass");
		$ticketLogin = build_url('user', 'edit', '', true) . '?ticket='.$user->ticket.'&user='.urlencode($user->username);
		$this->trackerService->track('password-reset', $email);
		
		if ($notify) {
			include_once 'Zend/Mail.php';

			$msg = "Hi $user->username,\n\r";
			$msg .= "A request was made to reset your password. To do so, please follow the following URL and reset it.\r\n";
			$msg .= "Login at $ticketLogin\r\n";
			
			$mail = new Zend_Mail();
			$mail->setBodyText($msg);
			$mail->setFrom(za()->getConfig('from_email'), za()->getConfig('name'));
			$mail->addTo($email);
			$mail->setSubject('Your account password');
			$mail->send();
		}

		return true;
	}
	
	/**
     * Grants access to a particular item
     * 
	 * @param object $item the item to remove access from
     * @param String $user The user to remove access for
     * @param String $role The role to remove (optional)
     */
    public function grantAccess($item, $user, $role)
    {
        $fields = array(
            'itemid' => $item->id,
            'itemtype' => get_class($item),
            'authority' => $user->getUsername(),
            'role' => $role,
        );
        $existing = $this->dbService->getByField($fields, 'UserRole');
        if ($existing != null) {
            return $existing;
        }
        
        $userrole = new UserRole();
        $userrole->itemid = $item->id;
        $userrole->itemtype = get_class($item);
        $userrole->authority = $user->getUsername();
        $userrole->role = $role;
        
        $this->dbService->saveObject($userrole);
    }
    
    /**
     * Remove access from a particular item 
     * 
     * @param object $item the item to remove access from
     * @param String $user The user to remove access for
     * @param String $role The role to remove (optional)
     */
    public function removeAccess($item, $user, $role=null)
    {
		if (!$item) {
			throw new Exception("Cannot remove access from null object");
		}
        $fields = array(
            'itemid' => $item->id,
            'itemtype' => get_class($item),
            'authority' => $user->getUsername(),
            
        );
        
        if ($role != null) {
            $fields['role'] = $role;
        }

        $existing = $this->dbService->getByField($fields, 'UserRole');
        if ($existing != null) {
            // delete away
            $this->dbService->delete($existing);
        }
    }
}
?>