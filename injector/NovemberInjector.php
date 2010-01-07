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


if (!function_exists('ifset')) {
	function ifset(&$array, $key, $default = null)
	{
	    if (!is_array($array) && !($array instanceof ArrayAccess)) throw new Exception("Must use an array!");
	    return isset($array[$key]) ? $array[$key] : $default;
	}
}

class NovemberInjector
{
    private $serviceCache;

    private $serviceDirectories;
    
    private $injectMap;
    
    /**
     * A map of all the properties that should be automagically set on a 
     * service
     */
    private $autoProperties;
    
    /**
     * Create a new injector. 
     *
     * @param string|array $serviceDirectory The location to automatically
     * 						load services from. Can be a string or an array
     * 						for multiple locations. 
     */
    public function __construct($serviceDirectory = '')
    {
        if ($serviceDirectory != '') {
	        if (is_array($serviceDirectory)) {
	            $this->serviceDirectories = $serviceDirectory;
	        } else {
	            $this->serviceDirectories = array($serviceDirectory);
	        }
        }

        $this->injectMap = array();
        $this->serviceCache = array();
        $this->autoProperties = array();
    }
    
    /**
     * Adds a directory to load services from
     * @param $dir The directory to load services from
     */
    public function addServiceDirectory($dir)
    {
        $this->serviceDirectories[] = $dir;
    }
    
    /**
     * Add an object that should be automatically set on managed objects
     * @param string $property the name of the property
     * @param object $object the object to be set
     */
    public function addAutoProperty($property, $object)
    {
        $this->autoProperties[$property] = $object;
    }

    /**
     * Load services using the passed in configuration for those services
     *
     * @param array $config
     */
    public function loadServices($config = array())
    {
        $services = array();

        // include the file and get the 
        foreach ($this->serviceDirectories as $serviceDir) {
	        $dir = new DirectoryIterator($serviceDir);
	        foreach ($dir as $value) {
	            if ($value->isDot()) continue;
	            
	            // skip non php and hidden or swap files
	            if (strpos($value, '.php') === false || strpos($value, '.') === 0) continue;
	            
	            $name = substr($value, 0, strrpos($value, '.'));
	            include_once $serviceDir.'/'.$value;
	            $service = new $name;
	            if (method_exists($service, 'configure')) {
	                if (isset($config[$name])) {
	                    $service->configure($config[$name]);
	                }
	            }
	            
	            // Flag to know whether the service is a replacement of another
                // service
	            $replaced = false;
	            if (isset($config[$name])) {
	                // Find out if this service definition should 'replace' or take on
	                // a different name. For example, a base DbService or AuthService
	                // can be replaced by declaring CustomAuthService, then 'replace'ing 
	                // it via this mechanism so that it gets used wherever there's a 
	                // request for 'AuthService'
	                $replace = ifset($config[$name], 'replace', null);
	                if ($replace) {
	                    $name = $replace;
	                    $replaced = true;
	                }
	            } 
	            
	            if (!$replaced) {
	                // if there's already a service with this name defined, 
	                // we should just skip adding this one, as it might have
	                // already been replaced. 
	                if (isset($this->serviceCache[$name])) {
	                    continue;
	                }
	            }
	            
	            $this->serviceCache[$name] = $service;
	            $services[] = $service;
	        }
        }

        // so now match up any dependencies and inject away
        foreach ($this->serviceCache as $service) {
            $this->inject($service);
        }
    }
    
    /**
     * Inject $object with available objects from $availableObjects
     *
     * @param Injectable $object
     */
    public function inject($object)
    {
        $robj = new ReflectionObject($object);
        $mapping = ifset($this->injectMap, get_class($object), null);
        
        if (!$mapping) {
            $mapping = new ArrayObject();
	        $properties = $robj->getProperties();
	
	        foreach ($properties as $propertyObject) {
	            $origName = $propertyObject->getName();
	            $name = ucfirst($origName);
	            if (isset($this->serviceCache[$name])) {
	                // Pull the name out of the registry
                    $value = $this->serviceCache[$name];
	                $propertyObject->setValue($object, $value);
	                $mapping[$origName] = $value;
	            }
	        }
	        $this->injectMap[get_class($object)] = $mapping;
        } else {
            foreach ($mapping as $prop => $toInject) {
                $object->$prop = $toInject;
            }
        }
        
        foreach ($this->autoProperties as $property => $value) {
            if (!isset($object->$property)) {
                $object->$property = $value;
            }
        }

        // Call the 'injected' method if it exists
        if (method_exists($object, 'injected')) {
            $object->injected();
        }
    }
    
    /**
     * Does the given service exist?
     */
    public function hasService($name)
    {
        return isset($this->serviceCache[$name]);
    }
    
    /**
     * Register a service object with an optional name to register it as the
     * service for. Once registered, this DOES NOT inject the service.
     */
    public function registerService($service, $replace=null)
    {
        $registerAt = get_class($service);
        if ($replace != null) {
            $registerAt = $replace;
        }
        
        $this->serviceCache[$registerAt] = $service;
    }
    
    /**
     * Register a service with an explicit name
     */
    public function registerNamedService($name, $service)
    {
        $this->serviceCache[$name] = $service;
    }
    
    /**
     * Get a named service
     * @param $name the name of the service to retrieve
     */
    public function getService($name)
    {
        if ($this->hasService($name)) {
            return $this->serviceCache[$name];
        }
        throw new Exception("Service $name is not defined");
    }
}

?>