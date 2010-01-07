<?php
ini_set('memory_limit', '16M');
define('APP_DIR', dirname(__FILE__));

if (!defined('BASE_DIR')) {
    define('BASE_DIR', APP_DIR);
}

$config = array();
$config['timezone'] = 'Australia/Sydney';
date_default_timezone_set($config['timezone']);
session_save_path(dirname(__FILE__).'/sessions');
$config['name'] = "System";
$config['from_email'] = "admin@website.com";
$config['debug'] = true;
$config['log_queries'] = false;
$config['log_file'] = dirname(__FILE__).'/data/logs/application.log';
$config['route_config'] = 'routes.ini';
$config['modules'] = array (
    'external',
    'expenses',
  );

// Set if you need to use http auth for self referential urls
$config['http_user'] = 'readonly';
$config['http_pass'] = 'readuser';
$config['require_http_auth'] = false;

$config['services'] = array (
    'DbAuthService' => 
    array (
      'replace' => 'AuthService',
      'user_class' => 'CrmUser',
    ),
    'UserService' => 
    array (
      'user_class' => 'CrmUser',
    ),
    'AuthComponent' => 
    array (
      'authenticators' => 
      array (
        0 => 'DbAuthComponent',
        1 => 'LdapAuthComponent',
      ),
    ),
    'FileService' => 
    array(
        'root' => '/data/files',
    ),
    'SearchService' =>
    array(
        'index' => '/data/index',
    ),
);

$config['plugins'] = array(
    'AutoLoginPlugin' => array(),
    'PhpAuthPlugin' => array(),
    'AuthorizationPlugin' => 
    array (
        'default' => array (
            'default_roles' => 'User',
            'admin' => 'Power',
            'file' => 'ReadOnly',
            'leave' => array('save' => 'Admin', 'edit'=>'Admin', 'list'=>'Admin', 'changestatus'=>'Admin'),
            'user' => array(
//                 'register' => 'Guest',
            ),
            'index' => array(
                'index' => 'External',
            ),
        ),
        'external' => array(
            'default_roles' => 'External',
			'user' => array(    
                'password' => 'Guest',
            ),
        ),
        'expenses' => array(
            'default_roles' => 'Power',
            'file' => 'ReadOnly',
            'user_access' => true,
        ),
        'login_controller' => 'user',
        'login_action' => 'login',
    ),
    'LayoutPlugin' => 
    array (
      'master_layout' => 
      array (
        'default_layout' => 'new-layout.php',
      ),
      'external' => 
      array (
        'default_layout' => 'new-external-layout.php',
      ),
      'expenses' => 
      array (
        'default_layout' => 'new-expenses-layout.php',
      ),
      'layout_path' => 'views/layouts',
    ),
	'FlashPlugin' => array(),
	
	// 'NoRoutePlugin' => array(),
);

// Include the user config to allow for overrides
include_once BASE_DIR.'/user-config.php';

// Combine the two, making sure to 
if (isset($user_config['services'])) {
    $__services = array_merge($config['services'], $user_config['services']);
} else {
    $__services = $config['services'];
}

if (isset($user_config['plugins'])) {
    $__plugins = array_merge($config['plugins'], $user_config['plugins']);
} else {
    $__plugins = $config['plugins'];
}

$config = array_merge($config, $user_config);
$config['services'] = $__services;
$config['plugins'] = $__plugins;

?>
