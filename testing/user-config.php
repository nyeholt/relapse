<?php
$user_config = array(); // new array!
$user_config = array (
  'name' => 'November bootstrap',
  'from_email' => 'admin@website.com',
  'debug' => '1',
  'log_queries' => '0',
  array (
  ),
  'services' => 
  array (
    'DbAuthService' => 
    array (
      'replace' => 'AuthService',
      'user_class' => 'CrmUser',
    ),
    'DbService' => 
    array (
      'db_type' => 'PDO_MYSQL',
      'db_params' => 
      array (
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'relapsetesting',
        'profiler' => true,
      ),
    ),
    'AlfrescoFileService' => 
    array(
        // 'replace' => 'FileService',
        // 'alfresco_url' => 'http://localhost:8080/alfresco/api',
        'alfresco_user' => 'admin',
        'alfresco_pass' => 'admin',
    ),
    /*'LdapAuthComponent' => 
    array(
        'host' => 'localhost',
        'basedn' => 'dc=localhost,dc=com',
        'options' => array(
            LDAP_OPT_PROTOCOL_VERSION => 3,
            LDAP_OPT_REFERRALS => 0,
        ),
        'username' => 'readonly',
        'password' => 'readonly',
        'personClass' => 'person',
        // Indicates the user property mapping
        'mapping' => array(
            'username' => 'cn',
            'email' => 'mail',
        ),
    ),*/
  ),
  'plugins' => 
  array (
    'AuthorizationPlugin' => 
    array (
      'restrictions' => 
      array (
        'user' => 
        array (
          'edit' => 'User,Admin',
          'list' => 'Admin',
          'response' => 'Admin',
        ),
        'client' => 'User,Admin',
        'contact' => 'User,Admin',
        'index' => 'User,Admin',
        'project' => 'User,Admin',
        'task' => 'User,Admin',
        'timesheet' => 'User,Admin',
      ),
      'login_controller' => 'user',
      'login_action' => 'login',
    ),
    'LayoutPlugin' => 
    array (
      'master_layout' => 'master-view.php',
      'layout_path' => 'views/layouts',
    ),
  ),
  
  'project_task_list_size' => '10',
  'project_list_size' => '20',
); ?>