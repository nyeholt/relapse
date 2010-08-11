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
 * Defines what information is commonly available for
 * a user in the application. 
 *
 */
interface NovemberUser
{
    public function getId();
    public function getUsername();
    public function getTicket();
    public function getRole();
    public function isAdmin();
    public function getLastLogin();
    public function setLastLogin($date);
    public function hasRole($role);
}

?>