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
 * This custom dispatcher will inject the created controller
 * with services available from the Zend registry
 *
 */
class InjectingDispatcher extends Zend_Controller_Dispatcher_Standard
{
    
    /**
     * Store the controller path to load the controller from
     * This is used so we can use extension controllers transparently
     *
     * @var string
     */
    private $controllerPath = '';
    
    /**
     * Get the controller path being used
     *
     * @return string
     */
    public function getControllerPath()
    {
        return $this->controllerPath;
    }
    
    /**
     * Dispatch to a controller/action
     *
     * Overridden so that the created controller is injected
     * with services. 
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @return boolean
     * @throws Zend_Controller_Dispatcher_Exception
     */
    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (!$this->isDispatchable($request)) {
            if (!$this->getParam('useDefaultControllerAlways')) {
                require_once 'Zend/Controller/Dispatcher/Exception.php';
                throw new Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
            }

            $className = $this->getDefaultControllerClass($request);
        } else {

            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * Load the controller class file
         */
        $className = $this->loadClass($className);
        
        $__start = getmicrotime();
        
        /**
         * Instantiate controller with request, response, and invocation 
         * arguments; throw exception if it's not an action controller
         */
        $controller = new $className($request, $this->getResponse(), $this->getParams());
        if (!$controller instanceof Zend_Controller_Action) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception("Controller '$className' is not an instance of Zend_Controller_Action");
        }

        NovemberApplication::getInstance()->inject($controller);
        $controller->view = Zend_Registry::get(NovemberApplication::$ZEND_VIEW);

        /**
         * Retrieve the action name
         */
        $action = $this->getActionMethod($request);
        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');
        if (empty($disableOb)) {
            ob_start();
        }
        $controller->dispatch($action);
        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        za()->recordStat('injectingdispatcher::dispatched-'.get_class($controller).':'.$action, getmicrotime() - $__start);
        // Destroy the page controller instance and reflection objects
        $controller = null;
    }
    
    /**
     * Returns TRUE if the Zend_Controller_Request_Abstract object can be 
     * dispatched to a controller.
     *
     * Use this method wisely. By default, the dispatcher will fall back to the 
     * default controller (either in the module specified or the global default) 
     * if a given controller does not exist. This method returning false does 
     * not necessarily indicate the dispatcher will not still dispatch the call.
     *
     * @param Zend_Controller_Request_Abstract $action
     * @return boolean
     */
    public function isDispatchable(Zend_Controller_Request_Abstract $request)
    {
        $className = $this->getControllerClass($request);
        if (!$className) {
            return true;
        }

        $fileSpec    = $this->classToFilename($className);
        $this->controllerPath = $this->getDispatchDirectory();

        $test = $this->controllerPath . DIRECTORY_SEPARATOR . $fileSpec;
        $found = Zend_Loader::isReadable($test);
        if (!$found) {
            // scan the extensions
            $extensions = $this->getFrontController()->getControllerDirectory();
            foreach ($extensions as $dir) {
                $test = $dir.DIRECTORY_SEPARATOR.$fileSpec;
                if (Zend_Loader::isReadable($test)) {
                    $this->controllerPath = $dir;
                    $found = true;
                    break;
                }
            }
        }
        return $found;
    }
    
    /**
     * Load a controller class
     * 
     * Overridden to take into account the extension directory
     * location, so will search in that for a controller 
     * definition if none is found in the controllers directory.
     *
     * @param string $className 
     * @return string Class name loaded
     * @throws Zend_Controller_Dispatcher_Exception if class not loaded
     */
    public function loadClass($className)
    {
        Zend_Loader::loadFile($this->classToFilename($className), $this->controllerPath, true);

        if ('default' != $this->_curModule) {
            $className = $this->formatModuleName($this->_curModule) . '_' . $className;
        }

        if (!class_exists($className)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Invalid controller class ("' . $className . '")');
        }

        return $className;
    }
}
?>