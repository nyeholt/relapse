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

define('CACHE_LOCATION', BASE_DIR.'/data/cache/store');

/**
 * A simple cache service that stores data on disk
 * 
 * 
 */
class CacheService implements Configurable 
{
	/**
	 * Cache for 1 hour by default
	 *
	 * @var int
	 */
	private $expiry = 300;
	
	private $items;
	
    public function configure($config)
    {
        $this->expiry = ifset($config, 'expiry', $this->expiry);
    }
    
    /**
     * Cache an item
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiry
     * 			How many seconds to cache this object for (no value uses the configured default)
     */
    public function store($key, $value, $expiry=0)
    {
    	if ($expiry == 0) {
    		$expiry = $this->expiry;
    	}
    	$location = $this->getDiskLocation($key);
    	$entry = new CacheItem();
    	$entry->value = serialize($value);
    	$entry->expireAt = time() + $expiry;
    	$data = serialize($entry);
   		file_put_contents($location, $data);
    }

    private function getDiskLocation($key)
    {
    	$name = md5($key);
    	$dir = CACHE_LOCATION.'/'.mb_substr($name, 0, 5);
    	if (!is_dir($dir)) {
    		mkdir($dir, 0777, true);
    	}
    	return $dir.'/'.$name;
    }
    
    /**
     * Gets a cached value
     */
    public function get($key)
    {
    	$location = $this->getDiskLocation($key);
    	
    	if (file_exists($location)) {
    		$data = file_get_contents($location);
    		
    		$entry = unserialize($data);
    		// if the expire time is in the future
   			if ($entry->expireAt > time()) {
   				$this->log->debug("Cache hit on " . $key);
    			return unserialize($entry->value);
    		}
    		
    		$this->log->debug("Cache expire " . $key);
    		// if we got to here, we need to expire the value
    		$this->expire($key);
    	}
    	$this->log->debug("Cache miss on " . $key);
    	return null;
    }

    public function expire($key)
    {
    	$this->log->debug("Expiring $key");
    	$location = $this->getDiskLocation($key);
    	if (file_exists($location)) {
    		unlink($location);
    	}
    }
}

class CacheItem
{
	public $value;
	public $expireAt;
}

?>