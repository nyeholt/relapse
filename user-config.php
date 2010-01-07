<?php
$user_config = array(); // new array!
$user_config = array (
  'name' => 'Relapse',
  'from_email' => 'nyeholt@iinet.net.au',
  'site_domain' => 'http://localhost',
  'site_context' => '/relapse',
  'debug' => '1',
  'log_queries' => '0',
  'support_mail_server' => '192.168.11.73:995',
  'support_email_user' => 'support',
  'support_email_pass' => 'supp0rtm3',
  'services' => 
  array (
    'DbService' => 
    array (
      'db_type' => 'PDO_MYSQL',
      'db_params' => 
      array (
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'crm2',
        'profiler' => true,
      ),
    ),
    'AlfrescoFileService' => 
    array (
      'alfresco_user' => 'admin',
      'alfresco_pass' => 'admin',
      'aspects_to_apply' => 
      array (
        0 => '{simplecrm.model}privateable',
      ),
    ),
    'SearchService' => 
    array (
      'index' => '/data/index',
    ),
  ),
  'project_task_list_size' => '4',
  'project_list_size' => '4',
  'controller' => 'admin',
  'action' => 'saveconfig',
  'module' => 'default',
  'alfresco_api' => '',
  'days_leave' => '20',
  'leave_approvers' => 'marcus',
  'issue_group' => 'General',
  'smtp_server' => 'mail.iinet.net.au',
  'theme' => 'blacktop',
  'owning_company' => '1',
  'email_max_size' => '2000000',
  'leave_project' => '8',
  'smsuser' => '',
  'smspass' => '',
  'default_expense_project' => '',
); ?>