<?php


function ifset(&$array, $key, $default = null)
{
    if (!is_array($array) && !($array instanceof ArrayAccess)) throw new Exception("Must use an array!");
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Shortcut for NovemberApplication::getInstance()
 *
 * @return NovemberApplication
 */
function za()
{
    $instance = NovemberApplication::getInstance();
    if ($instance == null) { debug_print_backtrace();}
    return $instance;
}

function l($msg, $severity=null)
{
    error_log("DEPRECATED USAGE OF l(): $msg");
}

function set_view($view)
{
    Zend_Registry::set(NovemberApplication::$ZEND_VIEW, $view);
}

function print_backtrace($trace, $log = true)
{
    $func = 'print';
    if ($log) $func = 'error_log';
    
    
    foreach ($trace as $t) { 
    	if ($func == 'error_log') {
    		za()->log(ifset($t, 'line', 'unknown') . ':' . ifset($t, 'file', 'null'), Zend_Log::ERR);
    	} else {
        	$func(ifset($t, 'line', 'unknown') . ':' . ifset($t, 'file', 'null'));
    	} 
    }
    
}

/**
 * Shortcut for RequestHelper::getUser
 *
 * @return unknown
 */
function current_user()
{
    return za()->getUser();
}

function query_log()
{
    $app = za();

	if ($app->getConfig('log_queries')) {
	$profiler = $app->getService('DbService')->getProfiler();
	    $total = $profiler->getTotalNumQueries();
	    //za()->log("DB Query Log");
	    za()->log("\n\n\n***************************\n***************************\n\n");
	    $elapsed = 0;
	    for ($i = 0; $i < $total; $i++) {
	    	$profile = $profiler->getQueryProfile($i);
	    	$elapsed += $profile->getElapsedSecs();
	    	za()->log("\n".$profile->getElapsedSecs()."s\n".$profile->getQuery());
	    }
	    za()->log("Total of $total queries, $elapsed seconds in total");
	}
	    
}

function pradoStripSlashes(&$data)
{
    return is_array($data)?array_map('pradoStripSlashes',$data):stripslashes($data);
}

function endswith($Haystack, $Needle){
    // Recommended version, using strpos
    return strrpos($Haystack, $Needle) === strlen($Haystack)-strlen($Needle);
}

/**
 * Delete a file, or a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.3
 * @link        http://aidanlister.com/repos/v/function.rmdirr.php
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function rmdirr($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }
 
    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
 
    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
 
        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }
 
    // Clean up
    $dir->close();
    return rmdir($dirname);
}

function array_remove(&$arr, $index)
{
    if(isset($arr[$index])){
        array_splice($arr, $index, 1);
    }
}

function getmicrotime()
{
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}

/**
 * mb_string compatibility. This has been borrowed from the
 * dompdf library, and is mostly identical to many other of these
 * type of mb_ compatibility libraries
 */

if ( !function_exists("mb_strlen") ) {
  function mb_strlen($str) {
    return strlen($str);
  }
}

if ( !function_exists("mb_strpos") ) {
  function mb_strpos($haystack, $needle, $offset = 0) {
    return strpos($haystack, $needle, $offset);
  }
}

if ( !function_exists("mb_strrpos") ) {
  function mb_strrpos($haystack, $needle, $offset = 0) {
    return strrpos($haystack, $needle, $offset);
  }
}

if ( !function_exists("mb_substr") ) {
  function mb_substr($str, $start, $length = null) {
    if ( is_null($length) )
      return substr($str, $start);
    else
      return substr($str, $start, $length);
  }
}

if ( !function_exists("mb_strtolower") ) {
  function mb_strtolower($str) {
    return strtolower($str);
  }
}

if ( !function_exists("mb_strtoupper") ) {
  function mb_strtoupper($str) {
    return strtoupper($str);
  }
}

if ( !function_exists("mb_substr_count") ) {
  function mb_substr_count($haystack, $needle) {
    return substr_count($haystack, $needle);
  }
}

if ( !function_exists("mb_strrchr") ) {
  function mb_strrchr($haystack, $needle) {
    return strrchr($haystack, $needle);
  }
}



?>