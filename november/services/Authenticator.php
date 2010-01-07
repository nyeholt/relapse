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

interface Authenticator
{
    /**
     * Authenticate a username and password and return a $userClass
     * instance that represents that user.
     *
     * @param string $username
     * @param string $password
     * @param string $userClass
     * 
     * @return User
     */
    public function authenticate($username, $password, $userClass = 'User');
    
    /**
     * When a user is updated in the system, this method is called to 
     * broadcase to authentication mechanisms that the update has occurred. 
     * This allows authentication information to be updated in different
     * systems if that's desired.
     * 
     * $newPassword will only be populated when a user changes their password,
     * otherwise it will be null if just the user object has been updated. 
     *
     * @param User $user
     * @param string $newPassword
     */
    public function updateUser($user, $newPassword=null);
}
?>