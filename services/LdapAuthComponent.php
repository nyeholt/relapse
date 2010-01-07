<?php

/**
 * Authenticates against a database
 *
 */
class LdapAuthComponent implements Authenticator, Configurable
{
    /**
     * The configuration around ldap.
     *
     * @var unknown_type
     */
    private $config;

    /**
     * The user service is needed to either create a new
     * user or synchronise the LDAP user.
     *
     * @var UserService
     */
    public $userService;

    private $ldapConnection;

    public function configure($config)
    {
        $this->config = $config;
    }

    /**
     * Connect to ldap
     */
    public function getLdapConnection()
    {
        if ($this->config == null) {
            return null;
        }

        if ($this->ldapConnection == null) {
            $host = ifset($this->config, 'host', 'localhost');
            $port = ifset($this->config, 'port', null);
            $options = ifset($this->config, 'options', array());

            $this->ldapConnection = ldap_connect($host, $port);

            foreach ($options as $key => $value) {
                ldap_set_option($this->ldapConnection, $key, $value);
            }
        }

        $success = $this->bindConnection($this->ldapConnection);
        if (!$success) $this->ldapConnection = null;
        
        return $this->ldapConnection;
    }

    /**
     * Bind the ldap connection
     */
    protected function bindConnection($conn)
    {
        $bindUser = ifset($this->config, 'username', '');
        $bindPass = ifset($this->config, 'password', '');

        $this->log->debug(__CLASS__.':'.__LINE__.": Binding to ldap as $bindUser");
        // bind to ldap if needbe
        $result = @ldap_bind($conn, $bindUser, $bindPass);
        if (!$result) {
            $this->log->err(__CLASS__.':'.__LINE__.": Failed binding with user $bindUser: ".ldap_error($conn));
            return false;
        } else {
            $this->log->debug(__CLASS__.':'.__LINE__.": Bound to ldap as $bindUser");
        }
        return true;
    }

	public function authenticateTicket($username, $ticket)
	{
	}
	
    /**
     * Given a username and password,
     * validates the user and returns the
     * new object.
     * @param $username the name of the user
     * @param $password the entered password
     * @param $userClass The class of the expected user
     * @return boolean whether auth was successful
     */
    public function authenticate($username, $password, $userClass='User')
    {
        // Connect to LDAP.
        $conn = $this->getLdapConnection();
         
        if (!$conn) {
            $this->log->warn("No valid LDAP connection found");
            return null;
        }
        
        $baseDN = ifset($this->config, 'basedn', '');
        $personClass = ifset($this->config, 'personClass', 'person');
         
        $query = "(&(objectclass=$personClass)(cn=$username))";
        $result = @ldap_search($conn, $baseDN, $query);
        if ($result) {
            $entries = ldap_get_entries($conn, $result);
            if (!$entries['count']) return false;
            // Take the first one.
            $userEntry = $entries[0];
             
            $user = $userEntry['dn'];
             
            // try binding on the connection to make sure this user
            // is able to
            $auth = @ldap_bind($conn, $user, $password); // 'cn=marcus,ou=SomeUnit,dc=localhost,dc=com', 'arcus');
            	
            if ($auth) {
                // Now get the user from the db, or create a new account for them.
                $user = $this->synchroniseUser($userEntry);
                if ($user != null) {
                    $this->log->debug(__CLASS__.'-'.__LINE__.": LDAP: Authenticated user ".$user->username);
                }
                return $user;
            }
        } else {
            $this->log->debug("Failed authenticating with query $query");
        }

        return false;
    }

    /**
     * Create or update a user's account
     *
     * @param string $username
     * @param array $entry
     */
    private function synchroniseUser($entry)
    {
        $mapping = ifset($this->config, 'mapping', array());
        $username = ifset($entry, $mapping['username'], false);

        if (!$username) {
            throw new Exception("No username mapping found.");
        }
         
        // Cast back from the array representation
        $username = $username[0];
         
        // get all the user's details and map the relevant attributes... which we'll skip!
        $user = $this->userService->getUserByField('username', $username);
        if ($user == null) {
            $params = array();
             
            $params['username'] = $username;
            $params['password'] = md5(time() . rand(1, 10000));

            // Fill in mapped params
            $email = ifset($entry, $mapping['email'], null);
            if ($email) {
                $email = $email[0];
            } else {
                $email = $params['password'].'@null.com';
            }
            $params['email'] = $email;

            try {
                $user = $this->userService->createUser($params, false);
            } catch (Exception $e) {
                $this->log->err("Failed creating user for $username: ".$e->getMessage().': '.current_url()."\r\n".print_r($params, true));
                return null;
            }
        }
        za()->log(__CLASS__.':'.__LINE__." User is ".get_class($user));
        return $user;
    }

    /**
     * Update a user inside the ldap server. If the user doesn't yet exist,
     * we'll create a new object for them.
     */
    public function updateUser($user, $newPassword=null)
    {
        $this->log->debug(__CLASS__.':'.__LINE__.': Updating user '.$user->username);

        $conn = $this->getLdapConnection();

        if (!$conn) {
            $this->log->warn("No valid LDAP connection found");
            return null;
        }

        $baseDN = ifset($this->config, 'basedn', '');
        $personClass = ifset($this->config, 'personClass', 'person');
         
        // see if a user already exists for this object.
        $query = "(&(objectclass=$personClass)(cn=$user->username))";

         
        // Build up an associative array of ldapPropName => newValue
        $newUser = array(
        'objectclass' => $personClass,
        );

        // use the mapping array to figure out what goes where
        $mapping = ifset($this->config, 'mapping', array());

        foreach ($mapping as $userField => $ldapField) {
            $newUser[$ldapField] = $user->$userField;
            if ($newUser[$ldapField] == "") {
                $newUser[$ldapField] = " ";
            }
        }

        if ($newPassword) {
            $this->log->warn("Setting new LDAP user password");
            $newUser['userPassword'] = $newPassword;
        }

        $cn = $user->username;

        // now find out if we're updating or creating from new
        $this->log->debug("Searching for users to update with $query");
        $result = @ldap_search($conn, $baseDN, $query);

        // Flag to determine whether to create a new user or not
        $create = true;

        if ($result) {
            $entries = ldap_get_entries($conn, $result);
            if (ifset($entries, 'count', 0)) {
                $create = false;
                 
                // Take the first one.
                $userEntry = $entries[0];

                $userDn = $userEntry['dn'];

                $this->log->debug("Updating LDAP user with details: ".print_r($newUser, true));

                $r = @ldap_modify($conn, $userDn, $newUser);

                if (!$r) {
                    $this->log->err("Failed modifying ldap user: ".ldap_error($conn));
                    throw new Exception("Failed creating ldap user");
                }
            }
        }

        if ($create) {
            // Cannot create a user without a password!
            if ($newPassword == null) {
                throw new Exception("Cannot create new user without a password");
            }

            // create a new user under the $this->config['new_user_root'] if it's set.
            $userRoot = ifset($this->config, 'new_user_root', $baseDN);
            if (is_array($userRoot)) {
                $userRoot = ifset($userRoot, $user->getRole(), $baseDN);
            }

            $dn = "cn=$cn,$userRoot";
            $this->log->debug("Creating in ".$dn);
            $r = @ldap_add($conn, $dn, $newUser);

            if (!$r) {
                $this->log->err("Failed creating ldap user: ".ldap_error($conn));
                throw new Exception("Failed creating ldap user");
            }
        }
    }
}

?>