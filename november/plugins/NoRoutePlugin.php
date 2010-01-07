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
class NoRoutePlugin extends Zend_Controller_Plugin_Abstract 
{ 

    public function preDispatch( Zend_Controller_Request_Abstract $request ) 
    { 
        $dispatcher = Zend_Controller_Front::getInstance() ->getDispatcher(); 
        $controllerName = $request->getControllerName(); 
        if (empty($controllerName)) { 
            $controllerName = $dispatcher->getDefaultController(); 
        } 
        
        $className = $dispatcher ->formatControllerName($controllerName); 
        if ($className) { 
            try { 
                // if this fails, an exception will be thrown and 
                // caught below, indicating that the class can’t 
                // be loaded. 
                Zend_Loader::loadClass($className, $dispatcher->getControllerDirectory()); 
                $actionName = $request->getActionName(); 
                if (empty($actionName)) { 
                    $actionName = $dispatcher->getDefaultAction(); 
                } 
                $methodName = $dispatcher ->formatActionName($actionName); 
                $class = new ReflectionClass( $className ); 
                if( $class->hasMethod( $methodName ) ) { 
                    // all is well - exit now 
                    return; 
                } 
            } catch (Zend_Exception $e) { 
                // Couldn’t load the class. No need to act yet, 
                // just catch the exception and fall out of the 
                // if 
            } 
        }
        // we only arrive here if can’t find controller or action 
        $request->setControllerName('noroute'); 
        $request->setActionName('index'); 
        $request->setDispatched( false ); 
    } 
}
?>