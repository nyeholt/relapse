<?php

define('BASE_DIR', dirname(dirname(__FILE__)));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/data/logs/error.log');

set_include_path(get_include_path().PATH_SEPARATOR.BASE_DIR);

include_once 'config.php';
include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}

include_once 'november/NovemberApplication.php';
include_once 'controllers/BaseController.php';

$app = NovemberApplication::getInstance($config);

$app->init();

$userService = za()->getService('UserService');
/* @var $userService UserService */
$user = $userService->getUserByField('username', 'admin');

if (!$user) {
	$params = array(
		'username'=>'admin',
		'email'=>'admin@admin.com',
		'password'=>'admin',
	);
	$userService->createUser($params, true, User::ROLE_ADMIN);
}

?>