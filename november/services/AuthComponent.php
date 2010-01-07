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
 * The auth component provides a method to chain
 * together authentication techniques. If you do NOT
 * want chained authentication, provide your own service
 * and use the 'replace' => 'AuthComponent' directive to
 * replace the declaration of this one. 
 *
 */
class AuthComponent implements Configurable, Authenticator
{
    private $authenticators; 
    
    /**
     * Save the list of authenticators
     * 
     * This list is the name of other services that 
     * provide the authenticator interface.
     *
     * @param array $config
     */
    public function configure($config)
    {
        if (!isset($config['authenticators'])) {
            throw new Exception("Missing authenticator definition");
        }
        $this->authenticators = $config['authenticators'];
    }
    
    /**
     * Authenticate a username and return the user's class (or false)
     *
     * @param string $username
     * @param string $password
     * @param string $userClass
     * @return NovemberUser | boolean
     */
    public function authenticate($username, $password, $userClass = 'User')
    {
        foreach ($this->authenticators as $authClass) {
            $component = za()->getService($authClass);
            $this->log->debug(__CLASS__.':'.__LINE__." Authenticating $username via $authClass");
            if ($component == null) {
                throw new Exception("Authentication component $authClass has not been defined");
            }
            $result = $component->authenticate($username, $password, $userClass);
            if ($result instanceof NovemberUser) {
                return $result;
            }
        }
        
        return false;
    }
    
    public function authenticateTicket($username, $ticket)
    {
    	foreach ($this->authenticators as $authClass) {
            $component = za()->getService($authClass);
            $this->log->debug(__CLASS__.':'.__LINE__." Authenticating $username via $authClass");
            if ($component == null) {
                throw new Exception("Authentication component $authClass has not been defined");
            }
            $result = $component->authenticateTicket($username, $ticket);
            if ($result instanceof NovemberUser) {
                return $result;
            }
        }
        
        return false;
    }

    /**
     * When updated, loop all authenticators and call their update
     */
    public function updateUser($user, $newPassword=null)
    {
        foreach ($this->authenticators as $authClass) {
            $component = za()->getService($authClass);
            if ($component == null) {
                throw new Exception("Authentication component $authClass has not been defined");
            }
            $result = $component->updateUser($user, $newPassword);
        }
    }
}
?>