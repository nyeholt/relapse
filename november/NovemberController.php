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
 * Subclass that adds a better redirection
 * method. 
 *
 */
class NovemberController extends Zend_Controller_Action 
{
    const RETURN_URL = 'RETURN_URL';
    
    /**
     * The view object we're rendering into. Placed here
     * for injectoring purposes. 
     *
     * @var CompositeView
     */
    public $view;
    
    /**
     * The dbService for our semi-scaffolding
     *
     * @var DbService
     */
    public $dbService;

	/**
	 * Delete actions must be validated
	 */
	protected $validateActions = array(
		'deleteaction' => true
	);
    
    /**
     * Redirect to a given controller/action/param url
     *
     * @param string $controller
     * @param string $action
     * @param string $params
     */
    protected function redirect($controller=null, $action=null, $params=null, $module=null)
    {
        //$this->_redirectPrependBase = false;
        // Don't auto exit because we want our plugins to execute still
        //$this->_redirectExit = false;
        if ($this->_getParam('_ajax') && !isset($params['_ajax'])) {
			$params['_ajax'] = 1;
		}

        $options = array('exit'=> false,'prependBase'=>false);
        $this->_redirect(build_url($controller, $action, $params, false, $module), $options);
    }
    
    /**
     * Redirect with all parameters from the current request
     *
     * @param string $controller
     * @param string $action
     */
    protected function redirectWithParams($controller=null, $action=null)
    {
        $this->redirect($controller, $action, $this->_getAllParams());    
    }
    
    /**
     * Checks to see whether or not there needs to be any request method
     * filtering before executing $action.
     * 
     * @param string $action Method name of action
     * @return void
     */
    public function dispatch($action)
    {
		try {
			$this->filterRequest($action);
			$this->validateRequestAction($action);
			parent::dispatch($action);
		} catch (Exception $e) {
			// add a 500 header
			if (!headers_sent()) {
				header('HTTP/1.1 500 Internal Server Error');
				echo $e->getMessage();
			}
			throw $e;
		}
    }
    
    /**
     * if an action is marked as validateable, then we need to check to make
     * sure that a valid token was passed through. 
     */
    protected function validateRequestAction($action)
    {
        if (isset($this->validateActions)) {
            if (isset($this->validateActions[mb_strtolower($action)])) {
                // check the session for the token that we're looking for
                $session = za()->getSession(); 
                $token = $session->novemberValidationToken;
                // check to make sure it matches that in the request
                $this->log->debug("Validating $action with token $token against ".$this->_getParam('__validation_token')); 
                if ($token == null || ($token != $this->_getParam('__validation_token'))) {
                    // invalid request bucko!
                    throw new Exception("Request validation failed");
                }
            }
        }
    }

    /**
     * Filters the request to remove any parameters that
     * shouldn't be set. 
     */
    protected function filterRequest($action)
    {
        if (isset($this->allowedMethods)) {
            // get the ones that are allowed for this request
            $allowed = ifset($this->allowedMethods, $action);

            if ($this->_request instanceof Zend_Controller_Request_Http) {
                if (strlen($allowed) && mb_strtolower($allowed) != mb_strtolower($this->_request->getMethod())) {
                    throw new InvalidRequestMethodException($this->_request->getMethod()." is an invalid request method for $action");
                }
            }

            /*if (mb_strtolower($allowed) == 'post' && isset($_GET)) {
                // knock out anything found in GET
				foreach ($_GET as $key => $value) {
				    $this->_setParam($key, null);
				}
            } else if (mb_strtolower($allowed) == 'get' && isset($_POST)) {
                foreach ($_POST as $key => $value) {
				    $this->_setParam($key, null);
				}
            }*/
        }
    }
    
    /**
     * Get the calling URL
     */
    protected function getCallingUrl()
    {
		return $this->_getParam('__return_url', ifset($_SERVER, 'HTTP_REFERER'));
    }
    
    /**
     * Set a return URL into the session. 
     *
     * @param unknown_type $url
     */
    protected function setReturnUrl($url)
    {
        $_SESSION[self::RETURN_URL] = $url;
    }
    
    /**
	 * Redirect to the session managed supplied return 
	 * url or back to the homepage.
	 *
	 */
	protected function returnOrHome()
	{
		if (isset($_SESSION[self::RETURN_URL])) {
			$url = $_SESSION[self::RETURN_URL];
			unset($_SESSION[self::RETURN_URL]);
			$options = array('exit'=> false,'prependBase'=>false);
			$this->_redirect($url, $options);
		} else {
		    $options = array('exit'=> false,'prependBase'=>true);
			$this->_redirect('/', $options);
		}
	}
	
    /**
     * Render a view
     *
     * @param string $view
     */
    protected function renderView($script)
    {
        $this->getResponse()->appendBody($this->view->render($script));
    }
    
    /**
     * Render a view ensureing it has no master view
     * wrapped around it. 
     *
     * @param string $view
     */
    protected function renderRawView($script)
    {
        $response = $this->view->clearMaster()->render($script);
        $this->getResponse()->appendBody($response);
    }

	/**
	 * package up a JSON response
	 *
	 * @param mixed $data
	 * @param string $message
	 * @param boolean $success
	 * @return string
	 */
	protected function ajaxResponse($data, $message = 'Success', $success = true) {
		$response = array(
			'data' => $data,
			'message' => $message,
			'success' => $success
		);

		return Zend_Json::encode($response);
	}
    
    /**
     * Flash something up to the user.
     *
     * @param string $string
     */
    protected function flash($string)
    {
    	$this->view->flash($string);
    }
    
    /**
     * Force this request to login
     */
    protected function requireLogin($controller='user', $action='login')
    {
        $request = $this->getRequest();
        /* @var $request Zend_Controller_Request_Abstract */
        $url = build_url($request->getControllerName(), $request->getActionName(), $this->_getAllParams(), false, $request->getModuleName());
        $var = self::RETURN_URL;
        za()->getSession()->$var = $url;
        // $action->setModuleName(ifset($this->config, 'login_module', 'default'));
        $request->setControllerName($controller);
        $request->setActionName($action);
        // Set dispatched to false so it'll re-do the request for the login. 
        $request->setDispatched(false);
    }
    
    /**
     * Get the type of model this controller stands for
     */
    protected function modelType()
    {
        return ucfirst($this->_request->getControllerName());
    }
    
    /**
     * View a model
     */
    public function viewAction()
    {
        $this->view->model = $this->byId();
        $this->prepareForView($this->view->model);
        $this->renderView($this->_request->getControllerName().'/view.php');
    }
    
    protected function prepareForView($model) {}
    
    public function indexAction()
    {
        $this->listAction();
    }
    
    /**
     * List all model types
     */
    public function listAction()
    {
        $modelType = $this->modelType();
        $this->view->items = $this->dbService->getObjects($modelType);
        $this->renderView($this->_request->getControllerName().'/list.php');
    }
    
    /**
     * When creating a new client, show this action
     *
     */
    public function editAction($model=null)
    {
        // figure out the model type based on the
        // name of this controller. 
        $modelType =  $this->modelType();
        
        if ($model == null) {
            if ((int) $this->_getParam('id')) {
                $this->view->model = $this->getModel(); //  $this->dbService->getById((int)$this->_getParam('id'), $modelType);
            } else {
                $this->dbService->typeManager->includeType($modelType);
                $this->view->model = new $modelType();
                za()->inject($this->view->model);
            }
        } else {
            $this->view->model = $model;
        }

        $this->prepareForEdit($this->view->model);

		if ($this->_getParam('_ajax')) {
			$this->view->viaajax = 1;
			$this->renderRawView($this->_request->getControllerName().'/edit.php');
		} else {
			$this->renderView($this->_request->getControllerName().'/edit.php');
		}
    }
    
    /**
     * Get the model object
     */
    protected function getModel()
    {
        return $this->byId();
    }
    
    /**
     * Prepare a view for editing
     */
    protected function prepareForEdit($model) {}

    /**
     * Will automatically attempt to save an object when
     * called
     */
    public function saveAction($modelType='')
    {
        if ($modelType == '') {
            $modelType =  $this->modelType();
        }

        $model = null;
        try {
            $params = $this->filterParams($this->_getAllParams());
            $model = $this->saveObject($params, $modelType);
        } catch (InvalidModelException $ime) {
            $this->flash($ime->getMessages());
            $model = new $modelType();
            $model->bind($this->_getAllParams());
            $this->saveFailedAction($model);
            return;
        }

        $this->onModelSaved($model);
    }
    
    /**
     * Execute the action when a save fails. 
     */
    protected function saveFailedAction($model)
    {
        $this->editAction($model);
    }

    /**
     * Saves an object. is declared as a protected method to allow
     * subclasses to override how parameters are saved if needbe. 
     */
    protected function saveObject($params, $modelType)
    {
        return $this->dbService->saveObject($params, $modelType);
    }
    
    /**
     * Deletes the specified object. 
     */
    public function deleteAction()
    {
        if ((int) $this->_getParam('id')) {
            $model = $this->byId(); 
            if ($model) {
                $this->dbService->delete($model);
            }
        } else {
            throw new Exception("No object specified");
        }

        $this->onModelDeleted($model);
    }
    
    /**
     * Filter out any parameters that should be filtered
     * 
     * Unset any string empty values, as that probably
     * means that we entered nothing. If it's actually
     * allowed for an empty string, we'll force child
     * classes to override and set empty strings themselves,
     * as this is the less frequent use case
     *
     * @param Array $params
     */
    protected function filterParams($params = null)
    {
        if ($params == null) $params = $this->_getAllParams();
        
        foreach ($params as $key => $value) {
            if ($params[$key] === '') {
                unset($params[$key]);
            }
        }

        return $params;
    }
    
    /**
     * Shortcut to getting an object by id
     *
     * @param int $id
     * @param string $type
     * @return mixed
     */
    protected function byId($id = null, $type='')
    {
        if (is_null($id)) $id = $this->_getParam('id');
        if ($type == '') $type =  $this->modelType();
        if (!$id) {
            return null;
        }
        return $this->dbService->getById((int) $id, $type);
    }
    
    /**
     * Called to redirect after saving a model object
     *
     */
    protected function onModelSaved($model)
    {
        $this->redirect($this->_request->getControllerName());
    }

    /**
     * Called after an object is deleted
     *
     * @param the deleted object $model
     */
    protected function onModelDeleted($model)
    {
    }
    
    /**
     * Redirect to the error page with a particular message
     *
     * @param string $message
     */
    protected function error($message='Unknown Error')
    {
        $this->flash($message);
        $this->redirect('error');
    }
}
?>