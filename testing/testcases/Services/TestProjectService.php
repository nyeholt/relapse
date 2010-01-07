<?php

class TestProjectService extends UnitTestCase  
{
	public function testCreate()
	{
	    $projectService = za()->getService('ProjectService');
	    /* @var $projectService ProjectService */
	    $userService = za()->getService('UserService');
	    /* @var $userService UserService */
	    $this->assertTrue(true);
	    
	    $user = $userService->getUser(1);

	    $sheet = $projectService->getSummaryTimesheet($user);
	    
	    // print_r($sheet);
	}
	
	public function testImportGantt()
	{
	    $projectService = za()->getService('ProjectService');
	    /* @var $projectService ProjectService */

	    $projectService->dbService->delete('project');
	    $projectService->dbService->delete('task');
	    $projectService->dbService->delete('usertaskassignment');
	    
	    $params['title'] = "My Project";
	    $project = $projectService->saveProject($params);
	    
	    $projectService->importTasks($project, dirname(__FILE__).'/POC.csv', 'ms');
	    
	    $exporter = $projectService->exportTasks($project, 'gp');

	    print_r($exporter->getHeaderRow());
	    while ($row = $exporter->getNextDataRow()) {
	        print_r($row);
	    }
	}
	
	public function testExportTasks()
	{

	}
}
?>