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

class TestAuthorizationPlugin extends UnitTestCase 
{
    public function testAuthorization()
    {
        $app = za();
        $pluginConf = $app->getConfig('plugins');
        
        $conf = array (
					'restrictions' =>
					array(
						'user' => 
						array(
							'edit' => 'User,Admin',
							'list' => 'Admin',
						),
					),
					'login_controller' => 'testcontroller',
					'login_action' => 'testaction',
				);

        $plugin = new AuthorizationPlugin($conf);
        
        $action = new Zend_Controller_Request_Http();
        
        $action->setControllerName("user");
        $action->setActionName('edit');
        
        $plugin->preDispatch($action);
        
        // Make sure it's redirected to the user / login controller
        $this->assertEqual($action->getControllerName(), 'testcontroller');
        $this->assertEqual($action->getActionName(), 'testaction');
        
        
        // Now test that a user with role User is fine
        $user = new User();
        za()->setUser($user);
        
        $user->role = 'User';
        
        $action->setControllerName("user");
        $action->setActionName('edit');
        
        $plugin->preDispatch($action);
        
        $this->assertEqual($action->getControllerName(), 'user');
        $this->assertEqual($action->getActionName(), 'edit');
        
        // Make sure they can't LIST
        $action->setControllerName('user');
        $action->setActionName('list');
        
        $plugin->preDispatch($action);
        
        $this->assertEqual($action->getControllerName(), 'testcontroller');
        $this->assertEqual($action->getActionName(), 'testaction');
        
        // Now make them an admin, make sure they can do both the above
        $user->role = 'Admin';
        
        $action->setControllerName("user");
        $action->setActionName('edit');
        
        $plugin->preDispatch($action);
        
        $this->assertEqual($action->getControllerName(), 'user');
        $this->assertEqual($action->getActionName(), 'edit');
        
        // Make sure they can't LIST
        $action->setControllerName('user');
        $action->setActionName('list');
        
        $plugin->preDispatch($action);
        
        $this->assertEqual($action->getControllerName(), 'user');
        $this->assertEqual($action->getActionName(), 'list');
    }
    
}
?>