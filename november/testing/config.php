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

$config['name'] = "November test harness";

$config['from_email'] = "admin@website.com";
$config['debug'] = true;
$config['log_file'] = dirname(__FILE__).'/application.log';
$config['services_dir'] = dirname(__FILE__).'/services';

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
					);

$config['plugins'] = array(
						'AuthorizationPlugin' =>
						array (
							'restrictions' =>
							array(
								'user' => 
								array(
									'edit' => 'User,Admin',
									'list' => 'Admin',
								),
							),
							'login_controller' => 'user',
							'login_action' => 'login',
						),
						'LayoutPlugin' => 
						array (
							'master_layout' => 'master-view.php',
							'layout_path' => 'views/layouts',
						),
						'FlashPlugin' => array(),
						'CookieAutoLoginPlugin' => array(),
						
						// Custom plugins for chat
						'MarkOldSessions' => array(
							// what chance is there of marking
							// old sessions? read this as 
							// there being '<threshold> chance in 1000'
							'threshold' => 1,
						),
					);

?>