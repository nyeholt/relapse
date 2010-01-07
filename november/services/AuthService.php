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

/**
 * Manages both authentication and authorization
 *
 */
class AuthService
{
    /**
	 * The authentication component being used
	 *
	 * @var 
	 */
	public $authComponent;
	
	/**
	 * What's our user class?
	 *
	 * @var string
	 */
	protected $userClass = 'User';
	
	
	public function getUserClass()
	{
	    return $this->userClass;
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
        if ($authResult) {
            $this->setAuthenticatedUser($authResult);
        }
        return $authResult;
    }

    /**
     * Generates a random string for the user's ticket
     * @return string
     */
    protected function generateTicket($salt)
    {
        $ticket = md5(time().$salt);
        return $ticket;
    }
    
    /**
     * Sets the passed in user as the authenticated
     * user of the application. Will generate a ticket
     * for this user which will be used for subsequent
     * requests. 
     *
     * @param NovemberUser $user
     */
    public function setAuthenticatedUser(NovemberUser $user)
    {
        $user->ticket = $this->generateTicket($user->getUsername());
        
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
        return null;
    }
    
    /**
     * Resets a user's password
     * @return boolean whether the user was found
     */
    public function resetPassword($email)
    {
        
    }
    
    /**
     * Log out a user
     */
    public function endSession($user = null)
    {
        if ($user == null) $user = za()->getUser();
        
        $guestUser = za()->getConfig('guest_user_class', 'GuestUser');
        za()->setUser(new $guestUser());
        Zend_Session::destroy();
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
        
    }
    
    /**
     * Remove access from a particular item 
     * 
     * @param object $item the item to remove access from
     * @param String $user The user to remove access for
     * @param String $role The role to remove (optional)
     */
    public function removeAccess($item, $user, $role='')
    {
        
    }
    
}
?>