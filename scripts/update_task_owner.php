<?php
umask(0002);
define('APP_DIR', dirname(dirname(__FILE__)));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');

// This is where the common library of helpers is located for Zend Framework things.
// At the moment it's a global path just so it's available for all ZF apps
// $globalLib = 'd:/www/common-php-lib';
$generalIncludes = 'd:/www/includes';
set_include_path(get_include_path().PATH_SEPARATOR.$generalIncludes.PATH_SEPARATOR.APP_DIR);

include_once APP_DIR.'/config.php';

include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}


include_once 'november/NovemberApplication.php';

$app = NovemberApplication::getInstance($config);
$app->init();

$dbService = za()->getService('DbService');
$projectService = za()->getService('ProjectService');
// Okay, need to first get the DB, select all the tasks, and change the assigned users
// to an array. Next, need to re-save the task to make sure the assigned users
// are correct in the usertaskassignment table. 
$select = $dbService->select();
$select->from('task', array('id', 'userid'));

$result = $dbService->query($select, null);

$rows = new ArrayObject($result->fetchAll(Zend_Db::FETCH_ASSOC));

foreach ($rows as $row) {
    echo "Checking user ".$row['userid']."\n";
    if (mb_strpos($row['userid'], '{') === false) {
        
        $task = $projectService->getTask($row['id']);
        echo "Changing ".$task->title."\n";
        $task->userid = array($row['userid']);
        $projectService->saveTask($task);
    }
    
    echo "\n\n";
}

?>