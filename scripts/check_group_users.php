<?php
umask(0002);
define('BASE_DIR', dirname(dirname(__FILE__)));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');

// This is where the common library of helpers is located for Zend Framework things.
// At the moment it's a global path just so it's available for all ZF apps
// $globalLib = 'd:/www/common-php-lib';

include_once 'config.php';
ini_set('memory_limit', '64M');

include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}


include_once 'november/NovemberApplication.php';
$name = ifset($_SERVER['argv'], 1); 
if (!mb_strlen($name)) {
    exit("Must supply a name to check\n");
}


$app = NovemberApplication::getInstance($config);
$app->init();

$groupService = za()->getService('GroupService');
/* @var $groupService GroupService */
$group = $groupService->getGroupByField('title', $name); 
$users = $groupService->getUsersInGroup($group);
foreach ($users as $user) {
    echo "$user->id - $user->username\n";
}


if ($app->getConfig('log_queries')) {
$profiler = za()->getService('DbService')->getProfiler();
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
?>