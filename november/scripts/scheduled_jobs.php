<?php

umask(0002);
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');
ini_set('memory_limit', '128M');

if (!isset($_SERVER['argv'][1])) {
    // failure! 
    exit("You must provide the application instance root directory\n");
}

define('BASE_DIR', $_SERVER['argv'][1]);

ini_set('include_path', get_include_path().PATH_SEPARATOR.BASE_DIR);

include_once 'config.php';
include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}


include_once 'november/NovemberApplication.php';

$app = NovemberApplication::getInstance($config);
$app->init();


$tasks = $app->getService('ScheduledTasksService');

$clear = ifset($_SERVER['argv'], 2, '');
if ($clear == 'clear') {
    $tasks->clearLock();
}

$tasks->run();

query_log();

?>
