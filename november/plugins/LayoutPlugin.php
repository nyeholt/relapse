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
class LayoutPlugin extends Zend_Controller_Plugin_Abstract
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
	 * @return Zend_Controller_Dispatcher_Token|boolean
	 */
	public function preDispatch($action) 
	{
	    $module = $action->getModuleName();
	    $definition = null;
	    if (isset($this->config[$module])) {
	        $definition = $this->config[$module];
	    } else if (isset($this->config['master_layout'])) {
		    $definition = $this->config['master_layout'];
	    }

	    if ($definition != null && !$action->getParam('__ignorelayout')) {
	        $view = null;

		    $layoutName = $this->getViewNameFrom($definition, strtolower($action->getControllerName()), strtolower($action->getActionName()));

            if ($layoutName) {
                $view = new MasterView($layoutName, $this->config['layout_path']);
            }
		    
		    if ($view != null) {
    			Zend_Registry::set('MasterView', $view);
    			$baseView = Zend_Registry::get(NovemberApplication::$ZEND_VIEW);
    			$baseView->setMaster('MasterView');
		    }
		}

		return $action;
	}
	
	/**
	 * Gets the layout from a passed in array of layout definitions
	 * 
	 * Definitions are of the form
	 * 
	 * array('controller' => string) where string is the layout for ALL
	 *     of that controller's actions
	 * array('controller' => array('action' => string) where string is
	 *     the layout just for that action in that controller.
	 *
	 * @param map $layoutDefinitions
	 */
	private function getViewNameFrom($layoutDefinitions, $controllerName, $actionName)
	{
	    if (is_string($layoutDefinitions)) return $layoutDefinitions;
	    
	    // figure out based on the controller and action which layout to use. 
        // if it's not set, use the one in the 'default_layout' key.
        $default = ifset($layoutDefinitions, 'default_layout');
        $layoutName = ifset($layoutDefinitions, $controllerName, $default);
        
        // If there's a layout set
        if ($layoutName) {
            // if a user has specified a different layout per action
            if (is_array($layoutName)) {
                // If the layout is set for that action, use it
                if (isset($layoutName[$actionName])) {
                    $layoutName = $layoutName[$actionName];
                } else {
                    // otherwise use the default layout
                    $layoutName = $default;
                }
            }
            return $layoutName;
        }
        
        return null;
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