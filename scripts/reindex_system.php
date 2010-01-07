<?php
umask(0002);
define('BASE_DIR', dirname(dirname(__FILE__)));

ini_set('memory_limit', '128M');
ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL | E_NOTICE);
ini_set('error_log', dirname(__FILE__).'/error.log');

include_once BASE_DIR.'/config.php';

include_once 'Zend/Loader.php';

function __autoload($class)
{
    Zend_Loader::loadClass($class);
}


include_once 'november/NovemberApplication.php';

$app = NovemberApplication::getInstance($config);
$app->init();

$dbService = za()->getService('DbService');

$projectService = za()->getService('ProjectService');
$clientService = za()->getService('ClientService');
$issueService = za()->getService('IssueService');

/* @var $issueService IssueService */
/* @var $clientService ClientService */
/* @var $projectService ProjectService */
$items = $clientService->getClients();
reindex($items);
unset($items);

$items = $clientService->getContacts();
reindex($items);
unset($items);

$items = $projectService->getprojects();
reindex($items);
unset($items);

$items = $projectService->getTasks();
reindex($items);
unset($items);

$items = $issueService->getIssues();
reindex($items);
unset($items);


function reindex($items) {
    $searchService = za()->getService('SearchService');
	foreach ($items as $item) {
	    echo "Reindexing ".$item->title."...   ";
	    $searchService->index($item);
	    echo "Done\r\n";
	}
}

?>