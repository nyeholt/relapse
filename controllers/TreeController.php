<?php

class TreeController extends NovemberController 
{
	/**
	 * ProjectService
	 *
	 * @var ProjectService
	 */
	public $projectService;
	
	/**
	 * @var FeatureService
	 */
	public $featureService;
	
	/**
	 * @var IssueService
	 */
	public $issueService;
	
	/**
	 * @var ItemLinkService
	 */
	public $itemLinkService;
	
	public function viewAction()
	{
		$parentType = $this->_getParam('type');
		$parentId = $this->_getParam('id');
		 
		$this->view->id = $parentType.'-'.$parentId;
		$this->renderRawView('tree/tree.php');
	}
    
    function dataAction()
    {
    	$root = $this->_getParam('root');
    	if (!$root || $root == 'source') {
    		$root = $this->_getParam('id');
    	} 

    	$bits = split('-', $root);
    	$childData = array();
    	// Are we getting child data bits, or are we getting direct objects of children?
		if (count($bits) >= 2) {
	    	$object = $this->byId($bits[1], $bits[0]);
	    	$options = ifset($bits, 2);

	    	$method = 'getChildData'.get_class($object);
	    	if (method_exists($this, $method)) {
	    		$childData = $this->$method($object, $options);
	    	} 
		}
		
		echo Zend_Json_Encoder::encode($childData);
    }
    
    protected function getChildDataClient(Client $client, $options = null)
    {
    	$projects = $this->projectService->getProjects(array('clientid='=>$client->id, 'ismilestone=' => 0, 'parentid='=>0));
    	$data = array();
    	foreach ($projects as $project) {
    		$item = new stdClass;
    		$item->id = get_class($project).'-'.$project->id;
	    	$item->text = $project->title . ' <a href="'.build_url('project', 'view', array('id'=>$project->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
	    	$item->expanded = false;
	    	$item->classes = "project-item iconed";
	    	$item->hasChildren = true;
	    	$data[] = $item;
    	}
    	return $data;
    }
    
    protected function getChildDataIssue($issue, $options = null) 
    {
    	$data = array();
    	switch ($options) {
    		case 'tasks': 
    			$items = $this->itemLinkService->getLinkedItemsOfType($issue, 'from', 'Task', array('task.complete='=> 0));
    			foreach ($items as $child) {
    				$item = new stdClass;
		    		$item->id = get_class($child).'-'.$child->id.'';
		    		
		    		ob_start();
		    			$this->view->percentageBar($child->getPercentage());
		    		$bar = ob_get_clean();

			    	$item->text = $bar.$child->title . ' <a href="'.build_url('task', 'edit', array('id'=>$child->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
			    	$item->expanded = false;
			    	$item->classes = "tree-task iconed";
			    	$item->hasChildren = false;
			    	$data[] = $item;
    			}
    			break;
    			break;
    		default: 
    			break;
    	}
    	
    	return $data;
    }
    
	protected function getChildDataFeature(Feature $feature, $options = null) 
    {
    	return $this->getChildDataIssue($feature, $options);
    }
    
    protected function getChildDataProject(Project $project, $options = null) 
    {
    	$data = array();
    	switch ($options) {
    		
    		case 'subprojects':
    			$children = $project->getSubProjects();
    			foreach ($children as $child) {
    				$item = new stdClass;
		    		$item->id = get_class($child).'-'.$child->id;
			    	$item->text = $child->title . ' <a href="'.build_url('project', 'view', array('id'=>$child->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
			    	$item->expanded = false;
			    	$item->classes = "project-item iconed";
			    	$item->hasChildren = true;
			    	$data[] = $item;
    			}
    			break;
    		case 'milestones':
    			$children = $project->getMilestones();
    			foreach ($children as $child) {
    				$item = new stdClass;
		    		$item->id = get_class($child).'-'.$child->id.'-tasks';
			    	$item->text = $child->title . ' <a href="'.build_url('project', 'view', array('id'=>$child->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
			    	$item->expanded = false;
			    	$item->classes = "tree-milestone iconed";
			    	$item->hasChildren = true;
			    	$data[] = $item;
    			}
    			break;
    		case 'features':
	            $items = $this->featureService->getFeatures(array('projectid='=>$project->id));
    			foreach ($items as $child) {
    				$item = new stdClass;
		    		$item->id = get_class($child).'-'.$child->id.'-tasks';
			    	$item->text = $child->title . ' <a href="'.build_url('feature', 'edit', array('id'=>$child->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
			    	$item->expanded = false;
			    	$item->classes = "tree-feature iconed";
			    	$item->hasChildren = true;
			    	$data[] = $item;
    			}
    			break;
    		case 'requests':
    			$items = $this->issueService->getIssues(array('projectid='=>$project->id, 'status <> ' => Issue::STATUS_CLOSED));
    			foreach ($items as $child) {
    				$item = new stdClass;
		    		$item->id = get_class($child).'-'.$child->id.'-tasks';
			    	$item->text = $child->title . ' <a href="'.build_url('issue', 'edit', array('id'=>$child->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
			    	$item->expanded = false;
			    	$item->classes = "tree-request iconed";
			    	$item->hasChildren = true;
			    	$data[] = $item;
    			}
    			break;
    		case 'tasks':
    			$items = $this->projectService->getTasks(array('projectid='=>$project->id, 'complete='=>0));
    			foreach ($items as $child) {
    				$item = new stdClass;
		    		$item->id = get_class($child).'-'.$child->id.'';
		    		
		    		ob_start();
		    			$this->view->percentageBar($child->getPercentage());
		    		$bar = ob_get_clean();
		    		
			    	$item->text = $bar.$child->title . ' <a href="'.build_url('task', 'edit', array('id'=>$child->id)).'"><img src="'.resource('images/bullet_go.png').'" /></a>';
			    	$item->expanded = false;
			    	$item->classes = "tree-task iconed";
			    	$item->hasChildren = false;
			    	$data[] = $item;
    			}
    			break;
    		default: 
    			$subProjects = $project->getSubProjects();
    			if (count($subProjects)) {
	    			$option = new stdClass;
	    			$option->text = 'Sub Projects';
	    			$option->id = get_class($project).'-'.$project->id.'-subprojects';
	    			$option->classes = "tree-folder iconed";
	    			$option->hasChildren = true;
	    			$data[] = $option;
    			}
    			
    			if ($project->hasMilestones()) {
	    			$option = new stdClass;
	    			$option->text = 'Milestones';
	    			$option->id = get_class($project).'-'.$project->id.'-milestones';
	    			$option->classes = "tree-folder iconed";
	    			$option->hasChildren = true;
	    			$data[] = $option;
    			}
    			
    			// see if there are features
				$items = $this->featureService->getFeatures(array('projectid='=>$project->id));
				if (count($items)) {
	    			$option = new stdClass;
	    			$option->text = 'Features';
	    			$option->id = get_class($project).'-'.$project->id.'-features';
	    			$option->classes = "tree-folder iconed";
	    			$option->hasChildren = true;
	    			$data[] = $option;
				}
    			
    			// see if there are requests
    			$items = $this->issueService->getIssues(array('projectid='=>$project->id, 'status <> ' => Issue::STATUS_CLOSED));
    			if (count($items)) {
	    			$option = new stdClass;
	    			$option->text = 'Requests';
	    			$option->id = get_class($project).'-'.$project->id.'-requests';
	    			$option->classes = "tree-folder iconed";
	    			$option->hasChildren = true;
	    			$data[] = $option;
    			}
    			break;
    	}
    	return $data;
    	
    }
    
    function stuff() {
    	
    	// $this->_response->setHeader('Content-type', 'text/javascript');
        ?>
[
	{
		"text": "1. Pre Lunch (120 min)",
		"expanded": true,
		"classes": "filetree",
		"children":
		[
			{
				"text": "1.1 The State of the Powerdome (30 min)"
			},
		 	{
				"text": "1.2 The Future of jQuery (30 min)"
			},
		 	{
				"text": "1.2 jQuery UI - A step to richnessy (60 min)"
			}
		]
	},
	{
		"text": "2. Lunch  (60 min)"
	},
	{
		"text": "3. After Lunch  (120+ min)",
		"children":
		[
			{
				"text": "3.1 jQuery Calendar Success Story (20 min)"
			},
		 	{
				"text": "3.2 jQuery and Ruby Web Frameworks (20 min)"
			},
		 	{
				"text": "3.5 Server-side JavaScript with jQuery and AOLserver (20 min)"
			},

		 	{
				"text": "3.6 The Onion: How to add features without adding features (20 min)",
				"id": "36",
				"hasChildren": true

			}
		]

	}

]
        <?php 
    }
}