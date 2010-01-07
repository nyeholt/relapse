<?php

define('APP_DIR', dirname(__FILE__));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');

// This is where the common library of helpers is located for Zend Framework things.
// At the moment it's a global path just so it's available for all ZF apps
// $globalLib = 'd:/www/common-php-lib';
$generalIncludes = 'd:/www/includes';
// $globalLib = dirname(__FILE__).'/november';

// set_include_path(get_include_path().PATH_SEPARATOR.$globalLib);
set_include_path(get_include_path().PATH_SEPARATOR.$generalIncludes);

include_once dirname(__FILE__).'/config.php';
include_once 'Zend.php';
include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}

include_once 'november/NovemberApplication.php';
include_once 'controllers/BaseController.php';

$app = NovemberApplication::getInstance($config);

$app->init();

$username = ifset($_SERVER['argv'], 1, null);
if (!$username) {
    echo "You must supply the username to make an admin\n";
    exit();
}

$role = ifset($_SERVER['argv'], 2, User::ROLE_USER);

$userService = za()->getService('UserService');
/* @var $userService UserService */
$user = $userService->getUserByField('username', $username);

if ($user) {
    $userService->setUserRole($user, $role);
}

if ($app->getConfig('debug')) {
    $profiler = za()->getService('DbService')->getProfiler();
    $total = $profiler->getTotalNumQueries();
    //za()->log("DB Query Log");
    for ($i = 0; $i < $total; $i++) {
    	$profile = $profiler->getQueryProfile($i);
    	//za()->log("\n".$profile->getElapsedSecs()."s\n".$profile->getQuery());
    }
}

?>