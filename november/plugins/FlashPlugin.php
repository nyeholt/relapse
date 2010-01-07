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
class FlashPlugin extends Zend_Controller_Plugin_Abstract
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
	 * @param  Zend_Controller_Request_Http |boolean $action
	 * @return Zend_Controller_Request_Http |boolean
	 */
	public function preDispatch($action) 
	{
		// If the session var is set, populate the view with that flash
		if (isset($_SESSION[CompositeView::$FLASH_KEY])) {
		    $view = Zend_Registry::get(NovemberApplication::$ZEND_VIEW);
			// Set the flash value 
			$view->flash($_SESSION[CompositeView::$FLASH_KEY], false);
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
	public function postDispatch($action) 
	{
	    $view = Zend_Registry::get(NovemberApplication::$ZEND_VIEW);
		if (!$view->isPersistentFlash()) {
			unset($_SESSION[CompositeView::$FLASH_KEY]);
		}
		return $action;
	}

	/**
	 * Called before Zend_Controller_Front exists its dispatch loop.
	 *
	 * @return void
	 */
	public function dispatchLoopShutdown() {}
}
?>