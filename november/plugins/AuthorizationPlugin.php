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
class AuthorizationPlugin extends Zend_Controller_Plugin_Abstract
{
	private $config;
	
	/**
	 * When build, apply the following config elements. 
	 *
	 * @param unknown_type $config
	 */
	public function __construct($config)
	{
		$this->config = $config;
	}
	
	/**
	 * Called before Zend_Controller_Front begins evaluating the
	 * request against its routes.
	 *
	 * @return void
	 */
	public function routeStartup() {}

	/**
	 * Called after Zend_Controller_Front exits from the router.
	 *
	 * This callback allows for proxy or filter behavior.  The
	 * $action must be returned for the Zend_Controller_Dispatcher_Token to enter the
	 * dispatch loop.  To abort before the dispatch loop is
	 * entered, return FALSE.
	 *
	 * @param  Zend_Controller_Dispatcher_Token|boolean $action
	 * @return Zend_Controller_Dispatcher_Token|boolean
	 */
	public function routeShutdown($action) {return $action;}

	/**
	 * Called before Zend_Controller_Front enters its dispatch loop.
	 * During the dispatch loop.
	 *
	 * This callback allows for proxy or filter behavior.  The
	 * $action must be returned for the Zend_Controller_Dispatcher_Token to enter the
	 * dispatch loop.  To abort before the dispatch loop is
	 * entered, return FALSE.
	 *
	 * @param  Zend_Controller_Dispatcher_Token|boolean $action
	 * @return Zend_Controller_Dispatcher_Token|boolean
	 */
	public function dispatchLoopStartup($action) {return $action;}

	/**
	 * Called before an action is dispatched by Zend_Controller_Dispatcher.
	 *
	 * This callback allows for proxy or filter behavior.  The
	 * $action must be returned for the Zend_Controller_Dispatcher_Token to be dispatched.
	 * To abort the dispatch, return FALSE.
	 *
	 * @param  Zend_Controller_Request_Http $action
	 * @return Zend_Controller_Request_Http
	 */
	public function preDispatch($action) 
	{
	    
		/*@var $action Zend_Controller_Request_Http */
		$controllerName = strtolower($action->getControllerName());
		$actionName = strtolower($action->getActionName());
		$moduleName = strtolower($action->getModuleName());
		
		// Check for authorization.
		$app = NovemberApplication::getInstance();
		$currentUser = $app->getUser();
		
		if ($currentUser->getRole() == User::ROLE_LOCKED) {
		    $action->setControllerName(ifset($this->config, 'login_controller', 'user'));
		    $action->setActionName(ifset($this->config, 'login_action', 'login'));
			return $action;
		}

		// Get the restrictions for the current request (if any)
		$conf = ifset($this->config, $moduleName);
		
		$roles = '';
		
		if (is_string($conf)) {
		    $roles = $conf;
		} else if (is_array($conf)) {
		    // check for a default
		    $roles = ifset($conf, 'default_roles', '');
		    // If there's something in the controllername entry...
		    $controllerConf = ifset($conf, $controllerName, $roles);
		    if (is_array($controllerConf)) {
		        $roles = ifset($controllerConf, $actionName, $roles);
		    } else {
		        $roles = $controllerConf;
		    }
		}
        
		// Are there required roles to authenticate? 
		$loginRequired = false;

		za()->log(__CLASS__.':'.__LINE__." - Authorizing ".$currentUser->getUsername()." for roles $roles");
		if ($roles != '') {
		    $loginRequired = true;
            $roles = explode(',', $roles);
            // If the user has any of the roles, let them in
            foreach ($roles as $role) {
                if ($currentUser->hasRole($role)) {
                    return $action;
                }
            }
	    }
	    
	    // If we've got this far, then we should ask the DB if the current user has
        // access to the current module and controller
        // We're expecting user_access => 
        // user_role
        // OR array (controller => user_role)
        $userAccess = ifset($conf, 'user_access');
       
        if ($userAccess != null) {
            $loginRequired = true;
            // if it's a string, just get the access for the module
            $accessService = za()->getService('AccessService');

            // See if they have access to this module
            $access = $accessService->getAccessList($currentUser->getUsername(), $moduleName);
            if (count($access)) {
                // okay, they have access, so we're all cool
                return $action;
            }
        }

	    if ($loginRequired) {
            $url = build_url($controllerName, $actionName, $action->getUserParams(), false, $moduleName);
            $_SESSION[NovemberController::RETURN_URL] = $url;
            // $action->setModuleName(ifset($this->config, 'login_module', 'default'));
            $action->setControllerName($this->config['login_controller']);
			$action->setActionName($this->config['login_action']);
	    }

		return $action;
	}

	/**
	 * Called after an action is dispatched by Zend_Controller_Dispatcher.
	 *
	 * This callback allows for proxy or filter behavior.  The
	 * $action must be returned, otherwise the next action
	 * will not be dispatched.  To exit the dispatch loop without
	 * dispatching the action, return FALSE.
	 *
	 * @param  Zend_Controller_Dispatcher_Token|boolean $action
	 * @return Zend_Controller_Dispatcher_Token|boolean
	 */
	public function postDispatch($action) {return $action;}

	/**
	 * Called before Zend_Controller_Front exists its dispatch loop.
	 *
	 * @return void
	 */
	public function dispatchLoopShutdown() {}
}
?>