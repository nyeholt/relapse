<?php

umask(0002);
define('BASE_DIR', dirname(__FILE__));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/data/logs/error.log');
ini_set('memory_limit', '32M');

include_once 'config.php';
include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}


include_once 'november/NovemberApplication.php';
include_once 'controllers/BaseController.php';

$start = getmicrotime();
$app = NovemberApplication::getInstance($config);
try {
    $app->run();
} catch (Exception $e) {
    za()->log(current_url().": exited with exception : ".$e->getMessage()."\n".$e->getTraceAsString(), Zend_Log::ERR);
}

$end = getmicrotime();
query_log();

if ($app->getConfig('debug')) {
    za()->log("Request to ".current_url()." processed in ".($end-$start)."ms");
    $stats = za()->getStats();
    foreach ($stats as $stat => $times) {
    	foreach ($times as $time) {
    		za()->log("$stat in $time");
    	}
    }
}

?>