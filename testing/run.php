<?php
define('BASE_DIR', dirname(__FILE__));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');

// This is where the common library of helpers is located for Zend Framework things.
// At the moment it's a global path just so it's available for all ZF apps
// $globalLib = 'd:/www/common-php-lib';
$generalIncludes = 'd:/www/includes';

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');

include_once dirname(__FILE__).'/config.php';

set_include_path(get_include_path().PATH_SEPARATOR.$generalIncludes.PATH_SEPARATOR.APP_DIR);

include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}


include_once 'november/NovemberApplication.php';

$app = NovemberApplication::getInstance($config);
$app->init();

$groups = array();
$test_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'testcases';

$testcase = ifset($_SERVER['argv'], 1);
if (!$testcase) {
    $testcase = ifset($_GET, 'testcase');
}

$group = new GroupTest('Misc');
create_groups($test_dir, $group);

$reporter = php_sapi_name() == 'cli' ? 'TextReporter' : 'HtmlReporter';
// so we iterate over the directory, creating test groups as we go
// down the list
foreach ($groups as $testGroup) {
	$testGroup->run(new $reporter());
}

/**
 * Enter description here...
 *
 * @param unknown_type $path
 * @param GroupTest $toGroup
 */
function create_groups($path, $toGroup) 
{
	global $groups, $testcase;
	
	$over = new DirectoryIterator($path);
	foreach ($over as $directory) {
		
		if (strpos($directory, '.') === 0) {
			continue;
		}
		
		$item_name = $path . DIRECTORY_SEPARATOR . $directory;
		if (is_dir($item_name)) {
			$group_name = str_replace('_testcases_', '', str_replace(DIRECTORY_SEPARATOR, '_', str_replace(dirname(__FILE__), '', $item_name)));
			// create a group and pass it recursively
			$group = new GroupTest($group_name);
			create_groups($item_name, $group);
		} else {
		    if ($testcase != null && $testcase != $directory) continue;
			// Add the testcase to the current group
			/* @var $toGroup GroupTest */
			if (strrpos(strrev($item_name), 'php.') === 0) {
				$toGroup->addTestFile($item_name);
			}
		}
	}
	
	$groups[] = $toGroup;
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