<?php

define('APP_DIR', dirname(__FILE__));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');

// This is where the common library of helpers is located for Zend Framework things.
// At the moment it's a global path just so it's available for all ZF apps
// $globalLib = 'd:/www/common-php-lib';
$generalIncludes = 'd:/www/includes';

// set_include_path(get_include_path().PATH_SEPARATOR.$globalLib);
set_include_path(get_include_path().PATH_SEPARATOR.$generalIncludes);

include_once dirname(__FILE__).'/config.php';
include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}

include_once 'november/NovemberApplication.php';
$app = NovemberApplication::getInstance($config);

// okay, now on with the simple little application.
include_once 'Zend/Console/Getopt.php';

$opts = new Zend_Console_Getopt(array(
    'views|v' => 'Whether views should be generated automatically',
    'path|p=s' => 'The location of the application, defaults to the present working dir',
    'addtype|t=w' => 'The name of the type to create',
    'all|a' => 'Will auto generate views and controllers'
    ));

try {
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit;
}

$path = isset($opts->p) ? $opts->p : getcwd();
$path = rtrim($path, "\\/");

$type = $opts->getOption('t');
$model = $opts->getOption('m');
$views = $opts->getOption('v');
$all = $opts->getOption('a');

if ($all || $type) {
    createController($type, $all || $views);
}

function createController($name, $views)
{
    global $path;
    
    $model = array('name' => $name);
    $template = APP_DIR.'/templates/controller.php';
    $outputFile = $path.'/controllers/'.$name.'Controller.php';
    processTemplate($template, $model, $outputFile);
    echo "Creating controller $name\n";
    if ($views) {
        $model['modelType'] = strtolower($name);
        $outputFile = $path.'/views/'.$model['modelType'].'/list.php';
        $template = APP_DIR.'/templates/list.php';
        processTemplate($template, $model, $outputFile);
        $outputFile = $path.'/views/'.$model['modelType'].'/edit.php';
        $template = APP_DIR.'/templates/edit.php';
        processTemplate($template, $model, $outputFile);
        $outputFile = $path.'/views/'.$model['modelType'].'/view.php';
        $template = APP_DIR.'/templates/view.php';
        processTemplate($template, $model, $outputFile);
    }
}

function processTemplate($template, $model, $target)
{
    extract($model);
    ob_start();
    include $template;
    $output = ob_get_clean();
    
    if (!file_exists(dirname($target)) && !is_dir(dirname($target))) {
        if (!mkdir(dirname($target), null, true)) {
            exit("Failed creating ".dirname($target)); 
        }
    }


    if (file_exists($target)) {
        echo "File $target exists, skipping\n";
        return;
    }
    
    $fp = fopen($target, "w");
    if (!$fp) exit("Failed creating output file $target");
    
    fputs($fp, $output);
    fclose($fp);
}

function php()
{
    echo '<?php';
}

function unphp()
{
    echo '?>';
}

?>