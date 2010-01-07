<?php
define('APP_DIR', dirname(dirname(__FILE__)));

if (!defined('BASE_DIR')) {
    define('BASE_DIR', APP_DIR);
}

date_default_timezone_set('Australia/Sydney');
$config = array();
$config['name'] = "System";
$config['from_email'] = "admin@website.com";
$config['debug'] = true;
$config['log_file'] = dirname(__FILE__).'/application.log';

$config['services'] = array (
	'DbService' => array (
		'db_type' => 'PDO_SQLITE',
		'db_params' => array ('host' => 'localhost',
		                 'username' => 'sa',
		                 'password' => '',
		                 'dbname'   => 'test.db',
		                 'profiler' => true,
		                 ),
	),
	'UserService' =>
    array(
        'user_class' => 'CrmUser',
    ),
    'AuthComponent' =>
    array(
        'authenticators' => array(
            'DbAuthComponent',
            'LdapAuthComponent',
        )
    ),
    'SearchService' =>
    array(
        'index' => '/testing/data/index',
    ),
    'FileService' => 
    array(
        'root' => '/testing/data/files',
    ),
);

$config['plugins'] = array(
						'AuthorizationPlugin' =>
						array (
							'restrictions' =>
							array(
							),
							'login_controller' => 'user',
							'login_action' => 'login',
						),
						'LayoutPlugin' => 
						array (
							'layout_path' => 'views/layouts',
						),
						'FlashPlugin' => array(),
						'CookieAutoLoginPlugin' => array(),
						'NoRoutePlugin' => array(),
					);

// Include the user config to allow for overrides
include_once dirname(__FILE__).'/user-config.php';

// Combine the two, making sure to 
$__services = array_merge($config['services'], $user_config['services']);
$__plugins = array_merge($config['plugins'], $user_config['plugins']);

$config = array_merge($config, $user_config);
$config['services'] = $__services;
$config['plugins'] = $__plugins;

/*
<?php
/**
 * INSERT USER DEFINED CONFIGURATION HERE. 
 * 
 * This file can be edited by the admin controller, so do not
 * define any runtime configuration directives here; instead, use
 * the config.php for that. 
 


$config['name'] = "Meeting System";
$config['from_email'] = "admin@website.com";
$config['debug'] = true;
$config['questionnaire'] = '';

$config['services']['DbAuthService'] = array(
							'replace' => 'AuthService',
						);
$config['plugins']['MarkOldSessions'] = array(
							// what chance is there of marking
							// old sessions? read this as 
							// there being '<threshold> chance in 1000'
							'threshold' => 1,
						);

$config['plugins']['AuthorizationPlugin'] =
						array (
							'restrictions' =>
							array(
								'user' => 
								array(
									'edit' => 'User,Admin',
									'list' => 'Admin',
								),
								'chat' =>
								array(
								    'index' => 'User,Admin',
								    'viewlog' => 'Admin',
								),
								'admin' => 'Admin',
							),
							'login_controller' => 'user',
							'login_action' => 'login',
						);
$config['plugins']['LayoutPlugin'] =
						array (
							'master_layout' => 'master-view.php',
							'layout_path' => 'views/layouts',
						);
*/
?>