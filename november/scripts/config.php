<?php

$config = array();
$config['timezone'] = 'Australia/Sydney';
date_default_timezone_set($config['timezone']);
$config['name'] = "System";
$config['from_email'] = "admin@website.com";
$config['debug'] = true;
$config['log_queries'] = false;
$config['log_file'] = dirname(__FILE__).'/application.log';

$config['services'] = array (

);

$config['plugins'] = array (
	
  );

?>