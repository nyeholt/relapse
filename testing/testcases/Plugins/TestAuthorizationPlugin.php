<?php

class TestAuthorizationPlugin extends UnitTestCase 
{
    public function testAuthorization()
    {
        $app = za();
        za()->setUser(new GuestUser());
        $pluginConf = $app->getConfig('plugins');
        
        $conf = array (
					'default' =>
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
        $action->setModuleName('default');
        
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