<?php

/**
 * Authenticates against a database
 *
 */
class DbAuthComponent implements Authenticator 
{
    /**
     * The db service
     *
     * @var DbService
     */
    public $dbService;

    /**
	 * Given a username and password,
	 * validates the user and returns the 
	 * new object.
	 * @param $username thename of the user
	 * @param $password the entered password
	 * @param $userClass The class of the expected user
	 * @return boolean whether auth was successful
	 */
	public function authenticate($username, $password, $userClass='User')
	{
		$fields = array('username' => $username);
		$user = $this->dbService->getByField($fields, $userClass);
		if ($user != null && $user->id) {
			// check the salted password
			$storedPass = sha1($password.$user->salt);
			if ($storedPass == $user->password) {
				return $user;
			}
		}
		return false;
	}

    public function updateUser($user, $newPassword=null)
    {
    }
	
	public function authenticateTicket($username, $ticket)
	{
	}
}

?>