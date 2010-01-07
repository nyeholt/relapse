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
 * Just like build_url, but takes into account the passed in User object. 
 * 
 * The user object is queried to see what their default module should be
 * so that it can dynamically assign the correct module for any URLs 
 * generated. 
 * 
 * @param $user
 * @param $controller
 * @param $action
 * @param $params
 * @param $includeDomain
 * @param $module
 */
function user_url(User $user, $controller=null, $action=null, $params='', $includeDomain=false, $module=null)
{
    if ($module == null) {
        $module = $user->getDefaultModule();
    }

    if ($module == null) {
    	$module = 'default';
    }
    return build_url($controller, $action, $params, $includeDomain, $module);
} 

/**
 * Creates a URL that points to the given module, controller and action, 
 * with the given parameters.
 *
 * @param unknown_type $controller
 * @param unknown_type $action
 * @return unknown
 */
function build_url($controller=null, $action=null, $params='', $includeDomain=false, $module=null)
{
    if ($module == null) {
        $ctrl = Zend_Controller_Front::getInstance();
    	$request = $ctrl->getRequest();
    	/* @var $request Zend_Controller_Request_Abstract  */
    	$name = null;
    	if ($request) {
    	    $name = $request->getModuleName();
    	}
        
        if ($name && $name != 'default') {
            $module = $name;
        }
    } else if ($module == 'default') {
        $module = '';
    }
    
	$paramStr = '';
	// The appendage is for any # based params passed in
	$appendage = '';
	if (is_array($params)) {
		$sep = '';
		foreach ($params as $k => $v) {
		    if ($v instanceof ArrayObject || is_array($v)) {
		        continue;
		    }
			if (!strlen($v)) {
				continue;
			}
			if (strpos($v, '#') === 0) {
			    $appendage = $v;
			    continue;
			}
			$paramStr .= $sep.urlencode($k).'/'.urlencode($v);
			$sep = '/';
		}
	} else {
		$paramStr = $params;
	}
	// $url = $this->getContextPath().
	$url = context_path($includeDomain).
	    ($module != null ? '/'.$module : '') .
	    ($controller != '' ? '/'.$controller : '') .
		($action != '' ? '/'.$action : '') . 
		($paramStr != '' ? '/'.$paramStr : '');
		
	// Many browsers require the / on the end when working with
	// url rewrites, so lets make sure it has one. 
	if (strrpos($url, '/') != (strlen($url) - 1) && strpos($url, '?') === false) {
		$url .= '/';
	}

	$url .= $appendage;

	$protocol = get_protocol(ifset($_SERVER, 'SERVER_PROTOCOL'));

	return $url;
}

function encode_params($params=array(), $sep1, $sep2)
{
	$sep = '';
	$appendage = '';
	$paramStr = '';
	foreach ($params as $k => $v) {
	    if ($v instanceof ArrayObject || is_array($v)) {
	        // encode as $name[] = vals
			foreach ($v as $val) {
				$paramStr .= $sep . urlencode($k.'[]') . $sep2 . urlencode($val);
				$sep = $sep1;
			}
			continue;
	    }
		if (!strlen($v)) {
			continue;
		}
		if (strpos($v, '#') === 0) {
		    $appendage = $v;
		    continue;
		}
		$paramStr .= $sep.urlencode($k).$sep2.urlencode($v);
		$sep = $sep1;
	}
	if ($appendage != '') {
		$paramStr .= $appendage;
	}
	return $paramStr;
}

function url_login($username, $password, $url)
{
    // Find the :// and replace with ://user:pass@. If it doesn't contain ://, then 
    // just return the original url
    if (mb_strlen($username) && mb_strlen($password) && mb_strpos($url, '://')) {
        $url = str_replace('://', '://'.$username.':'.$password.'@', $url);
    }
    
    return $url;
}

function get_protocol($type)
{
    $https = ifset($_SERVER, 'HTTPS', 'off');
    if ($https == 'on') {
        return 'https';
    }
    if (!$type) return 'http';
    
    $prot = strtolower(substr($type, 0, strpos($type, '/')));
    return $prot;
}

/**
 * Get the current url with a list of additional parameters added
 *
 * @param array $params
 * @return string
 */
function current_url()
{
    if (!isset($_SERVER['REQUEST_URI'])) {
        return '/';
    }
	$pathIndex = dirname($_SERVER['SCRIPT_NAME']);

	// SubDirectoryRouter: remove $pathIndex from $_SERVER['REQUEST_URI']
	$path = str_replace($pathIndex, '', $_SERVER['REQUEST_URI']);
	$path = $_SERVER['REQUEST_URI'];
	if (strstr($path, '?')) {
		$path = substr($path, 0, strpos($path, '?'));
	}
	
	return $path;
}

function context_path($forceDomain=false)
{
	static $context_path;
	
	if ($context_path == null) {
	    if (isset($_SERVER['REQUEST_URI'])) {
       		$pathIndex = dirname($_SERVER['SCRIPT_NAME']);
		
			// SubDirectoryRouter: remove $pathIndex from $_SERVER['REQUEST_URI']
			$path = str_replace($pathIndex, '', $_SERVER['REQUEST_URI']);
			if (strstr($path, '?')) {
				$path = substr($path, 0, strpos($path, '?'));
			}
			$context_path = $pathIndex == '/' ? '' : $pathIndex;
	    } else {
	        $context_path = za()->getConfig('site_context', ''); 
	    }

		
		$protocol = get_protocol(ifset($_SERVER, 'SERVER_PROTOCOL'));
		
		// if we need to include the domain, do so now
        if ($forceDomain || $protocol == 'https') {
	        $domain = za()->getConfig('site_domain', null); 
		    if (!$domain) {
		    	$domain = $protocol.'://'.$_SERVER['SERVER_NAME'];
		    }
		    
		    $context_path = $domain.$context_path;
        }
	}

	return $context_path;
}

function referer()
{
    return ifset($_SERVER, 'REFERER', false);
}

function get_mime_content_type($filename)
{
    if (function_exists('mime_content_type ')) {
        return mime_content_type($filename);
    }
    
    $ext = substr($filename, strrpos($filename, '.') + 1);

    $extensions =
    array(
        "tgz"=>"application/x-gtar",
        "gz"=>"application/gzip",
        "tar"=>"application/x-tar",
        "zip"=>"application/zip",
        "gif"=>"image/gif",
        "jpeg"=>"image/jpeg",
        "jpg"=>"image/jpeg",
        "jpe"=>"image/jpeg",
        "png"=>"image/png",
        "tiff"=>"image/tiff",
        "tif"=>"image/tiff",
        "kdc"=>"image/x-kdc",
        //""=>"image/x-pcd",
        "mpeg"=>"video/mpeg",
        "mpg"=>"video/mpeg",
        "mpe"=>"video/mpeg",
        "mng"=>"video/x-mng",
        'php' => 'text/plain',
    );

    return ifset($extensions, $ext, 'application/octet-stream');
}

/**
 * Use a file from the resources folder
 */
function resource($stub)
{
	return context_path().'/resources/'.$stub;
}


/**
 * Use a file from a theme folder
 */
function theme_resource($stub)
{
	$theme = za()->getUser()->getTheme();
	if ($theme != '') {
		$theme = mb_strtolower($theme);
		return context_path().'/themes/'.$theme.'/resources/'.$stub;
	} else {
		return resource($stub);
	}
}

function encode_json($string)
{
	return Zend_Json::encode($string);
}

function decode_json($string)
{
	return Zend_Json::decode($string);
}
?>