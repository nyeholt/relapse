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

define('NOVEMBER_APP_DIR', dirname(__FILE__));

include_once NOVEMBER_APP_DIR.'/GuestUser.php';
include_once NOVEMBER_APP_DIR.'/Configurable.php';
include_once NOVEMBER_APP_DIR.'/CompositeView.php';
include_once NOVEMBER_APP_DIR.'/MasterView.php';
include_once NOVEMBER_APP_DIR.'/InjectingDispatcher.php';

include_once NOVEMBER_APP_DIR.'/MappedObject.php';

include_once NOVEMBER_APP_DIR.'/services/DbService.php';
include_once NOVEMBER_APP_DIR.'/services/TypeManager.php';

include_once NOVEMBER_APP_DIR.'/services/Authenticator.php';
include_once NOVEMBER_APP_DIR.'/services/AuthComponent.php';
include_once NOVEMBER_APP_DIR.'/services/AuthService.php';
include_once NOVEMBER_APP_DIR.'/services/SearchService.php';
include_once NOVEMBER_APP_DIR.'/services/ScheduledTasksService.php';
include_once NOVEMBER_APP_DIR.'/services/AccessService.php';
include_once NOVEMBER_APP_DIR.'/services/CacheService.php';
include_once NOVEMBER_APP_DIR.'/services/VersioningService.php';

include_once NOVEMBER_APP_DIR.'/NovemberController.php';

include_once NOVEMBER_APP_DIR.'/validators/InvalidModelException.php';
include_once NOVEMBER_APP_DIR.'/validators/ModelValidator.php';
include_once NOVEMBER_APP_DIR.'/validators/CVLValidator.php';
include_once NOVEMBER_APP_DIR.'/validators/UniqueValueValidator.php';

include_once NOVEMBER_APP_DIR.'/exceptions/InvalidRequestMethodException.php';

include_once NOVEMBER_APP_DIR.'/User.php';

include_once NOVEMBER_APP_DIR.'/web-helper.php';
include_once NOVEMBER_APP_DIR.'/utils.php';

include_once 'injector/NovemberInjector.php';

include_once 'Zend/Log.php';                  // Zend_Log base class

/**
 * Container class for a user. Not really used for anything other
 * than providing a few support methods that things like controllers
 * will use. 
 *
 */
class NovemberApplication
{
    const SYSTEM_CONFIG = 'user-config.php';
    
    public static $ZEND_VIEW = 'NovemberView';
    
    private $user;
    private $config;
    
    /**
     * @var NovemberInjector
     */
    private $injector;
    
    /**
     * An associative array that identifies each extension in the 
     * APP_DIR/extension folder.
     *
     * @var array
     */
    private $extensions;
    
    /**
     * The front controller to use. 
     *
     * @var Zend_Controller_Front
     */
    private $frontController;
    
    /**
     * The logger we're using
     *
     * @var Zend_Log
     */
    private $logger;
    
    
    /**
     * @return NovemberApplication
     */
    public static function getInstance($config=null)
    {
        static $instance; 
        if ($config != null) {
            $instance = new NovemberApplication($config);
        }
        return $instance;
    }

    /**
     * Create the application
     *
     * @param array $config
     */
    private function __construct($config)
    {        
        $this->config = $config;
        $this->injector = new NovemberInjector();
        
        // We define our include path to make sure that the 
        // user defined plugins can override the application ones. 
        set_include_path(get_include_path().PATH_SEPARATOR.NOVEMBER_APP_DIR.'/plugins');
        set_include_path(get_include_path().PATH_SEPARATOR.APP_DIR.'/plugins');
    }
    
    /**
     * Get a readonly list of the extensions
     *
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
    
    /**
     * Initialises the application
     *
     */
    public function init()
    {
    	$__start = getmicrotime();
        if (isset($this->config['log_file'])) {
            $writer = new Zend_Log_Writer_Stream($this->config['log_file']);
            $this->logger = new Zend_Log($writer);
            if (isset($this->config['log_format'])) {
                $formatter = new Zend_Log_Formatter_Simple($this->config['log_format']);
                $writer->setFormatter($formatter);
            }

            // If not in debug mode, hide debug log messages
            if (!ifset($this->config, 'debug', false)) {
                $this->logger->addFilter(Zend_Log::NOTICE);
            }
        }
        
        $this->recordStat('za::initlog', getmicrotime() - $__start);
        $__start = getmicrotime();
        
        $mailServer = $this->getConfig('smtp_server');
        if (mb_strlen($mailServer)) {
            ini_set('SMTP', $mailServer);
        }

        // Create a new view object.
        $view = new CompositeView();
        Zend_Registry::set(self::$ZEND_VIEW, $view);

        /* @var Zend_Controller_Front $controller */
        $this->frontController = Zend_Controller_Front::getInstance();
        $this->frontController->addControllerDirectory($this->getConfig('controller_dir', 'controllers'));
        
        $modules = ifset($this->config, 'modules', array());
        foreach ($modules as $module) {
            if (is_dir(APP_DIR.'/modules/'.$module)) {
                $this->frontController->addControllerDirectory('modules/'.$module, $module);
            }
        }

        $this->recordStat('za::initmodules', getmicrotime() - $__start);
        $__start = getmicrotime();
        
        $this->frontController->throwExceptions(ifset($this->config, 'debug', false) ? true : false);
        $this->frontController->setDispatcher(new InjectingDispatcher());
        
        if (isset($this->config['route_config']) && php_sapi_name() != 'cli') {
            $router = $this->frontController->getRouter();
            /* @var $router Zend_Controller_Router_Rewrite */
            $config = new Zend_Config_Ini($this->config['route_config'], 'routes');
            $router->addConfig($config, 'routes');
        }
        
        Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
        $this->frontController->setParam('noViewRenderer', true);
        
        /**
         * Set the session
         */
        $session_name = str_replace(' ', '_', $this->config['name']);
        
        Zend_Session::start(array('name' => $session_name));
        
        $this->injector->addAutoProperty('log', $this->logger);
        $this->injector->addServiceDirectory(ifset($this->config, 'services_dir', APP_DIR.'/services'));
        
        $__start = getmicrotime();
        
        $services = $this->loadDefaultServices();

        $this->recordStat('za::initdefaultservices', getmicrotime() - $__start);
        $__start = getmicrotime();
        
        foreach ($services as $defaultService) {
            $this->injector->inject($defaultService);
        }
        
        $this->recordStat('za::initinjectdefaultservices', getmicrotime() - $__start);
        $__start = getmicrotime();
        
        // Load extensions
        $this->loadExtensions();

        $this->recordStat('za::initloadext', getmicrotime() - $__start);
        $__start = getmicrotime();
        
        $this->injector->loadServices($this->config['services']);
        
        $this->recordStat('za::initloadservices', getmicrotime() - $__start);
        $__start = getmicrotime();
        
        // We know that there's definitely going to be an AuthService, 
        // so we can now go and load the user if there was one in 
        // the session.
        $auth = $this->injector->getService('AuthService');
        if (isset($this->getSession()->CURRENT_USER_TICKET) && mb_strlen($this->getSession()->CURRENT_USER_TICKET)) {
            $auth->validateTicket($this->getSession()->CURRENT_USER_NAME, $this->getSession()->CURRENT_USER_TICKET);
        }

        $this->recordStat('za::initvalidateauth', getmicrotime() - $__start);
    }
    
    /**
     * Load the extensions for the application
     *
     * WARNING: When using extensions, you MUST be careful not to name
     * one using the same name as one of the controllers. If you do, 
     * ZF will think you're trying to use modules... heh heh heh
     * 
     */
    protected function loadExtensions()
    {
        $this->extensions = array();
        $extDir = ifset($this->config, 'extensions_dir', APP_DIR.'/extensions');
        if (!is_dir($extDir)) return
        ;
        $dir = new DirectoryIterator($extDir);
        
        foreach ($dir as $value) {
            $dirname = $value->__toString();
            if ($dirname == '.' || $dirname == '..') continue;
            if (!is_dir($extDir.DIRECTORY_SEPARATOR.$dirname)) {
                continue;
            }
            
            $base = $extDir.DIRECTORY_SEPARATOR.$dirname.DIRECTORY_SEPARATOR;
            if (is_dir($base.'services')) {
                $this->injector->addServiceDirectory($base.'services');
            }
            
            if (is_dir($base.'controllers')) {
                // We add the 'ext' so that we don't clash with the module routing
                $this->frontController->addControllerDirectory($base.'controllers', 'ext-'.$dirname);
            }
            
            if (is_dir($base.'views')) {
                
                $view = Zend_Registry::get(self::$ZEND_VIEW);
                /* @var $view CompositeView */
                $view->addScriptPath($base.'views');
            }

            // add the extension
            $this->extensions[$dirname] = rtrim($base, '/');
        }
    }
    
    /**
     * There's some built in services that we're going to load first. 
     *
     * @return an array of services created
     */
    private function loadDefaultServices()
    {
        $services = new ArrayObject();
        $dbService = new DbService();
        if (isset($this->config['services']['DbService'])) {
            $dbService->configure($this->config['services']['DbService']);
            
        }
        $this->injector->registerService($dbService);
        $services[] = $dbService;

		$service = new TypeManager();
        if (isset($this->config['services']['TypeManager'])) {
            $service->configure(ifset($this->config['services'], 'TypeManager', array()));
        }
        $this->injector->registerService($service);
        $services[] = $service;
        
        $service = new SearchService();
        if (isset($this->config['services']['SearchService'])) {
            $service->configure($this->config['services']['SearchService']);
        }
        $this->injector->registerService($service);
        $services[] = $service;

        $authService = new AuthService();
        if (isset($this->config['services']['AuthService'])) {
            $authService->configure($this->config['services']['AuthService']);
        }
        $this->injector->registerService($authService);
        $services[] = $authService;

        $authComponent = new AuthComponent();
        if (isset($this->config['services']['AuthComponent'])) {
            $authComponent->configure($this->config['services']['AuthComponent']);
        }
        $this->injector->registerService($authComponent);
        $services[] = $authComponent;

        $tasksService = new ScheduledTasksService();
        $this->injector->registerService($tasksService);
        $services[] = $tasksService;
        
        $accessService = new AccessService();
        $this->injector->registerService($accessService);
        $services[] = $accessService;
        
        $cacheService = new CacheService();
		$this->injector->registerService($cacheService);
        $services[] = $cacheService;
    	if (isset($this->config['services']['CacheService'])) {
            $cacheService->configure($this->config['services']['CacheService']);
        }

		$service = new VersioningService();
		$this->injector->registerService($service);
        $services[] = $service;
    	if (isset($this->config['services']['VersioningService'])) {
            $service->configure($this->config['services']['VersioningService']);
        }

        return $services;
    }
    
    /**
     * Keep a mapping of what needs injecting based on the
     * type of the object.
     */
    private $injectMap = array();
    
    /**
     * Inject $object with available objects from $availableObjects
     *
     * @param Injectable $object
     */
    public function inject($object)
    {
        $this->injector->inject($object);
    }
    
    public function run()
    {
        $this->init();

        foreach ($this->config['plugins'] as $name => $config) {
            include_once $name.'.php';
            $plugin = new $name($config);
            $this->frontController->registerPlugin($plugin);
        
        }
        try {
        	$__start = getmicrotime(); 
            $this->frontController->dispatch();
            za()->recordStat('za::dispatch', getmicrotime() - $__start);
        } catch (Exception $e) {
            // record the current _GET and _POST variables
            $this->logger->err("Dispatch failed for ".current_url().": ".$e->getMessage().", get and post to follow");
            $this->logger->err(print_r($_REQUEST, true));
            throw $e;
        }
    }

    /**
     * The user is held in the application object
     * for quick and easy access
     *
     * @param ZendUser $user
     */
    public function setUser(NovemberUser $user)
    {
        $this->getSession()->CURRENT_USER_NAME = $user->getUsername();
        $this->getSession()->CURRENT_USER_TICKET = $user->getTicket();
        $this->user = $user;
    }

    /**
     * Retrieve the current user
     *
     * @return NovemberUser
     */
    public function getUser()
    {
        if ($this->user == null) {
            $guestUser = $this->getConfig('guest_user_class', 'GuestUser');
            $this->user = new $guestUser();
        }
        
        return $this->user;
    }
    
    /**
     * Retrieve a configuration parameter
     *
     * @param $param optional parameter to determine which config value to get. 
     *                  if not set, will return all config settings
     * @param $default optional default value to use if $param is not found
     */
    public function getConfig($param=null, $default=null)
    {
        if ($param === null) {
            return $this->config;
        }
        return ifset($this->config, $param, $default);
    }
    
    private $stats = array();
    
    public function recordStat($key, $time)
    {
    	$existing = ifset($this->stats, $key, array());
    	$existing[] = $time;
    	$this->stats[$key] = $existing;
    }
    
    public function getStats()
    {
    	return $this->stats;
    }
    
	/**
     * Set or override a configuration parameter
     *
     * @param $param parameter to determine which config value to set. 
     *                  if not set, will return all config settings
     * @param $default optional default value to use if $param is not found
     */
    public function setConfig($param, $value)
    {
    	$current = ifset($this->config, $param);
    	$this->config[$param] = $value;
    	return $current;
    }
    
    /**
     * Get a service
     */
    public function getService($service)
    {
        return $this->injector->getService($service);
    }
    
    /**
     * Set a service
     */
    public function setService($service)
    {
        $this->injector->registerService($service);
    }
    
    /**
     * Log something.
     *
     * @param string $msg
     * @param int $severity
     */
    public function log($msg=null, $severity = Zend_Log::DEBUG)
    {
        if ($msg == null) {
            return $this->logger;
        }
        $this->logger->log($msg, $severity);
    }
    
    private $session;
    
    /**
     * Get the current application session
     * @return Zend_Session_Namespace
     */
    public function getSession()
    {
        if ($this->session == null) {
            $this->session = new Zend_Session_Namespace();
        }

        return $this->session;
    }
}

if(get_magic_quotes_gpc())
{
    if(isset($_GET))
        $_GET=array_map('pradoStripSlashes',$_GET);
    if(isset($_POST))
        $_POST=array_map('pradoStripSlashes',$_POST);
    if(isset($_REQUEST))
        $_REQUEST=array_map('pradoStripSlashes',$_REQUEST);
    if(isset($_COOKIE))
        $_COOKIE=array_map('pradoStripSlashes',$_COOKIE);
}

?>