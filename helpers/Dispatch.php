<?php

class Helper_Dispatch extends NovemberHelper
{
    /**
     * We keep an array of already dispatched 
     * controller / action / module couplings
     * to ensure we don't recursively loop
     *
     * @var array
     */
    private static $__DISPATCHED = array();
    
    /**
     * Allows the dispatching and processing of a separate 
     * controller request from inside an existing view. 
     * 
     * Should be used for read only stuff (please!)
     *
     * @param string $controller
     * @param string $action
     * @param array $params A list of parameters to bind into the new request
     * @param string $module
     * @param array $requestParams A list of parameters to pull from the current request
     */
    public function Dispatch($controller, $action, $params=array(), $module=null, $requestParams=array())
    {
        
        // If no module, use the current request module
        $oldRequest = null;
        $ctrl = Zend_Controller_Front::getInstance();
	    $oldRequest = $ctrl->getRequest();
        if ($module == null) {
	        
	    	/* @var $request Zend_Controller_Request_Abstract  */
	    	$name = null;
	    	if ($oldRequest) {
	    	    $name = $oldRequest->getModuleName();
	    	}
	        
	        if ($name && $name != 'default') {
	            $module = $name;
	        }
	    } else if ($module == 'default') {
	        $module = '';
	    }

        $key = $controller.'|'.$action.'|'.$module;
        if (isset(self::$__DISPATCHED[$key])) {
            za()->log("Recursive dispatch detected $key ", Zend_Log::ERR);
            return;
        }
        
        self::$__DISPATCHED[$key] = true;
        
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        
        $request->setControllerName($controller);
        $request->setActionName($action);
        
        if ($params) {
            $request->setParams($params);
        }
        
        if (count($requestParams)) {
            foreach ($requestParams as $rp) {
                // get from the current request and stick into the new
                $value = $oldRequest->getParam($rp, '');
                $request->setParam($rp, $value);
            }
        }
        
        if ($module) {
            $request->setModuleName($module);
        }

    	$oldView = Zend_Registry::get(NovemberApplication::$ZEND_VIEW);
        
    	$allPaths = $oldView->getAllPaths();
    	
    	$newView = new CompositeView();
    	
    	foreach ($allPaths['script'] as $scriptPath) {
    	    $newView->addScriptPath($scriptPath);
    	}
    	
    	foreach ($allPaths['helper'] as $helper) {
    	    $newView->addHelperPath($helper['dir'], $helper['prefix']);
    	}
    	
    	foreach ($allPaths['filter'] as $filter) {
    	    $newView->addFilterPath($filter['dir'], $filter['prefix']);
    	}
    	
    	Zend_Registry::set(NovemberApplication::$ZEND_VIEW, $newView);
        
        $dispatcher = new InjectingDispatcher();

        $dispatcher->setParams($params)->setResponse($response);
        $request->setDispatched(true);
        $dispatcher->dispatch($request, $response);
        $response->outputBody();

        Zend_Registry::set(NovemberApplication::$ZEND_VIEW, $oldView);
    }
}