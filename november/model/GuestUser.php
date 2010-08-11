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
include_once dirname(__FILE__).'/NovemberUser.php';

class GuestUser implements NovemberUser 
{
    public function getUsername() { return 'Anonymous'; }
    public function getTicket() { return null; }
    public function getRole() { return "None"; }
    public function getId() { return 0; }
    public function isAdmin() { return false; }
    public function getLastLogin() { return date('Y-m-d H:i:s'); }
    public function setLastLogin($date) { }
    public function hasRole($role) { return $role == 'Guest'; }
    public function getTheme() { return za()->getConfig('theme', ''); }
}
?>