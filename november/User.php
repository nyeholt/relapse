<?php
/**
 * This file belongs to the November framework, an extension of the
 * Zend Framework, written by Marcus Nyeholt <marcus@mikenovember.com>
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to marcus@mikenovember.com so I can send you a copy immediately.
 *
 * @package   November
 * @copyright  Copyright (c) 2006-2007 Marcus Nyeholt (http://mikenovember.com)
 * @version    $Id$
 * @license    New BSD License
 */

class User extends Bindable implements NovemberUser 
{
	const ROLE_ADMIN = 'Admin';
	const ROLE_POWER = 'Power';
	const ROLE_USER = 'User';
	const ROLE_PENDING = 'Pending';
	const ROLE_GUEST = 'Guest';
	const ROLE_EXTERNAL = 'External';
	const ROLE_PUBLIC = 'Public';
	const ROLE_READONLY = 'ReadOnly';
	const ROLE_LOCKED = 'Locked';
	
	const LAST_LOGIN = '__LAST_LOGIN';
	
	public $id = 0;
	public $username = 'Anonymous';
	public $password;

	public $salt;
	
	public $ticket;

	public $role = User::ROLE_GUEST;

	public $email;

	public $created;
	
	public $lastlogin;

	public $firstname;
	
	public $lastname;
	
	/**
	 * The user's timezone for date formatting
	 */
	public $timezone;
	
	/**
	 * What format do the users want for their dates? 
	 *
	 * @var String
	 */
	public $dateformat = 'Y-m-d';
	public $longdateformat = 'F jS, Y';
	
	/**
     * What theme is the user using? 
     *
     * @var String
     */
    public $theme;
	
	public $constraints = array();
	public $searchableFields = array();
	
	private $roleMapping;

    public $defaultmodule = '';

	public function __construct()
	{
	    $this->created = date('Y-m-d H:i:s', time());
	    $this->constraints['username'] = new Zend_Validate_Regex('/^[a-z0-9=.@ _-]+$/i');
	    $this->constraints['__this'] = array(new UniqueValueValidator('username'));
	    $this->constraints['__this'][] = new UniqueValueValidator('email');
	    $this->roleMapping = array(
	        self::ROLE_LOCKED,
	        self::ROLE_GUEST,
	        self::ROLE_PENDING,
            self::ROLE_PUBLIC,
	        self::ROLE_EXTERNAL,
	        self::ROLE_READONLY,
	        self::ROLE_USER,
	        self::ROLE_POWER,
	        self::ROLE_ADMIN,
	    );
        $this->roleMapping = array_flip($this->roleMapping);
	}
	
    public function getUsername() { return $this->username; }
    public function getTicket() { return $this->ticket; }
    public function getRole() { return $this->role; }
    public function getId() { return $this->id; }
    public function isAdmin() { return $this->hasRole(User::ROLE_ADMIN); }
    public function isPower() { return $this->hasRole(User::ROLE_POWER); }

    public function getDefaultModule() { return $this->defaultmodule; } 
    
    public function getAvailableRoles()
    {
        return array_keys($this->roleMapping);
    }

    public function hasRole($role)
    {
        return $this->getRoleValue() >= $this->getRoleValue($role); 
    }
    
    /**
     * Get the value for a given role, or the current role
     */
    public function getRoleValue($role=-1)
    {
        if ($role == -1) $role = $this->role;
        
        $value = ifset($this->roleMapping, $role, 0);
        return $value;
    }
    
    /**
     * When getting the last login date, we don't want the
     * value from the database, but the session stored value
     * of what it used to be before the current session started,
     * so we'll pass that back if it exists.
     *
     * @return unknown
     */
    public function getLastLogin() 
    {
        return ifset($_SESSION, self::LAST_LOGIN, $this->lastlogin); 
    }
    
    /**
     * When we set the last login date, we want to make sure to
     * remember the last VISITED date (ie their last session's date),
     * which is actually the lastlogin before we set that value here
     *
     * @param unknown_type $date
     */
    public function setLastLogin($date) 
    {
        if (!isset($_SESSION[self::LAST_LOGIN])) {
            $_SESSION[self::LAST_LOGIN] = $this->lastlogin;
        }
        $this->lastlogin = $date; 
    }
    
    /**
     * Get the theme the current user has set
     * @return String the name of the theme to use
     */
    public function getTheme()
    {
    	if (mb_strlen($this->theme) && mb_strtolower($this->theme) != 'default') {
    		return mb_strtolower($this->theme);
    	}
    	
    	if (mb_strtolower($this->theme) == 'default') {
    		return '';
    	}

    	return  za()->getConfig('theme', '');
    }

    /**
     * Formats a given date to the user's preferred date format
     */
    public function formatDate($date)
    {
    	if (!$date) return '';
    	
    	$format = $this->dateformat ? $this->dateformat : 'F jS, Y';
        return date($format, strtotime($date));
    }
	
	/**
	 * Generate and set a salted password value for this user object
	 *
	 * @param String $rawPassword 
	 *			The raw password for the user that needs to be salted before
	 *			storage
	 */
	public function generateSaltedPassword($rawPassword)
	{
		$this->salt = sha1(uniqid(rand(), true));
		$this->password = sha1($rawPassword.$this->salt);
	}

    /**
	 *
	 * @param Datetime $date
	 *			The date to format
	 * @return String
	 */
    public function wordedDate($date)
    {
    	$format = $this->longdateformat ? $this->longdateformat : 'F jS, Y';
    	return date($format, strtotime($date));
    }
}
?>