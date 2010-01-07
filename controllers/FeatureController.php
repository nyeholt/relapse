<?php
include_once 'model/Feature.php';

class FeatureController extends BaseController 
{
    /**
     * The project service
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * FeatureService
     *
     * @var FeatureService
     */
    public $featureService;
    
    /**
     * The ItemLinkService
     *
     * @var ItemLinkService
     */
    public $itemLinkService;
    
    /**
     * When saved, redirect to either the project or client this is attached to
     *
     * @param  feature $model
     */
    public function onModelSaved($model)
    {
        // If there's a parent to be linked to, catch and set that here
        if ($this->_getParam('parentfeature')) {
            // link it using the item link service
            $parent = $this->byId($this->_getParam('parentfeature'));
            if ($parent) {
                $this->itemLinkService->parentChildLink($parent, $model);
            }
        }

        if ($model->projectid) {
            // Not editing
            $this->redirect('project', 'view', array('id'=>$model->projectid, '#features'));
        } else {
            $this->redirect('project');
        }
    }
    
    /**
     * 
     * 
     */
    public function readUpdateAction()
    {
    	$data = $this->getEditableInfo($this->_getParam('id'));
    	$feature = $this->byId(ifset($data, 'id'));
    	$field = ifset($data, 'field');
    	

    		if ($field == 'id') exit("Invalid Field");
    		$feature->$field = $this->_getParam('value');
    		$this->featureService->saveFeature($feature);
    		echo $this->view->wikiCode($feature->$field);
    		return;
    	// }
    	echo "Invalid request";
    }
    
    private function getEditableInfo($val)
    {
    	$bits = split('-', $val);
    	
    	$ret = array (
    		'id' => $bits[1],
    		'field' => $bits[2]
    	);
    	
    	return $ret;
    }
    
    /**
     * 
     */
    public function loadFieldAction()
    {
    	$data = $this->getEditableInfo($this->_getParam('id'));
    	$feature = $this->byId(ifset($data, 'id'));
    	$field = ifset($data, 'field');

    	if (isset($feature->$field)) {
    		echo $feature->$field;
    	}
    }
    

    public function estimateAction()
    {
    	// first off, get all the completed features from the last few months and get the 
		// velocities
		$project = $this->byId(null, 'Project');
		$date = date('Y-m-d 00:00:00', strtotime('-6 months'));
		$where = array(
			'estimated <>' => 0,
			'created > ' => $date,
			'complete = ' => 1,
			'hours <> ' => 0,
		);
		$features = $this->featureService->getFeatures($where);
		
		$velocities = array();
		foreach ($features as $feature) {
			echo "Completed Feature: ".$feature->title." Est ".$feature->estimated." Hours: ".$feature->hours."<br/>";
			$spent = $feature->hours / za()->getConfig('day_length', 8);
			$velocity = $feature->estimated / $spent;
			if ($velocity < 3) {
				$velocities[] = $velocity;
			}
		}
		
		// if we don't have at least 100 velocities, lets get the max and min and make up some random ones
		// in between
		if (count($velocities) < 100) {
			
			$number = 100 - count($velocities);
			echo "Creating $number random velocities<br/>";

			// The max we'll allow is the actual estimate, or a velocity of 1
			$max = 10;
			$min = min($velocities) * 10;
			for ($i = 0; $i < $number; $i++) {
				$rand = mt_rand($min, $max);
				$velocities[] = $rand / 10;
			}
		}
		
		// lets simulate!
		$where = array(
			'projectid =' => $project->id,
			'estimated <>' => 0,
			'complete = ' => 0,
		);
		$todo = $this->featureService->getFeatures($where);
		$estimatedTodo = 0;
		foreach ($todo as $feature) {
			echo "Adding incomplete feature ".$feature->title." estimate of ".$feature->estimated."<br/>";
			$estimatedTodo += $feature->estimated;
		}
		echo "Estimated $estimatedTodo days left <br/>";

		$numVelocities = count($velocities);
		$possibilities = array();
		for ($i = 0; $i < 100; $i++) {
			$currentRun = 0;
			foreach ($todo as $feature) {
				$velocity = mt_rand(0, $numVelocities - 1);
				$velocity = $velocities[$velocity];
				
				// we take the estimate divided by the velocity
				$currentRun += $feature->estimated / $velocity;
			}
			
			// add it to the list of answers
			$possibilities[] = $currentRun;
		}
		
		$deviation = $this->findStandardDeviation($possibilities);
		$mean = $this->average($possibilities);
		
		echo "Mean: $mean, Deviation: $deviation<br/>";
		$lower = $mean - (2 * $deviation);
		$upper = $mean + (2 * $deviation);
		echo "$lower - $mean - $upper<br/>";
    }
    
    private function average($numbers)
    {
    	$total = 0;
		$count = count($numbers);
		foreach ($numbers as $number) {
			$total += $number;
		}

		return $total / $count;
    }
    
    /**
     * Compute the standard deviation
     * 
     */
    private function findStandardDeviation($numbers)
    {
    	$avg = $this->average($numbers);
		
		// now figure out how far each item is from the average
		$diffs = array();
		foreach ($numbers as $number) {
			$diffs[] = pow($number - $avg, 2);
		}
		
		$total = 0;
		foreach ($diffs as $number) {
			$total += $number;
		}
		
		// get the average of this
		$deviation = sqrt($total / (count($numbers) - 1));
		
		return $deviation;
    }
    
    /**
     * Load the features for display in the project page.
     *
     */
    public function projectListAction()
    {
        $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        
        $this->view->features = $this->featureService->getProjectFeatures($project);
        $this->view->project = $project;
        
        $this->renderRawView('feature/feature-list.php');
    }
    
    /**
     * Read the features in a full document like view
     */
    public function readAction()
    {
    	$project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        
        $this->view->features = $this->featureService->getProjectFeatures($project);
        $this->view->project = $project;
        
        $this->renderView('feature/read.php');
    }

    public function prepareForEdit($model)
    {
        $project = $this->projectService->getProject((int) $this->_getParam('projectid', $model->projectid));
        $parentFeature = $this->projectService->getFeature((int) $this->_getParam('parent'));
        
        if ($project == null) {
            $this->flash("Specified project not found");
            $this->renderView('error.php');
            return;
        }

        $this->view->project = $project;
        
        if ($parentFeature) {
            $this->view->parentfeature = $parentFeature->id;
        }
        
        if ($model->id) {
            $this->view->linkedToFeatures = $this->itemLinkService->getLinkedItems($model, 'from', 'Feature');
            $this->view->linkedFromFeatures = $this->itemLinkService->getLinkedItems($model, 'to', 'Feature');
        } 

        $this->view->projects = $this->projectService->getProjectsForClient($project->clientid);
        $this->view->projectFeatures = $this->featureService->getFeatures(array('projectid=' => $model->projectid));
        $this->view->projectTasks = $this->projectService->getTasks(array('projectid=' => $project->id), 'title asc');
        $this->view->priorities = array('Must Have', 'Should Have', 'Would Like', 'Nice To Have');
        $this->view->linkedTasks = $this->itemLinkService->getLinkedItemsOfType($model, 'from', 'Task');

        parent::prepareForEdit($model);
    }
    

    /**
     * Get a list of the new features
     *
     */
    public function featureListAction()
    {

        $from = za()->getUser()->getLastLogin();
        $type = $this->_getParam('type');
        $date = $type == 'new' ? 'created' : 'updated';
        $this->view->features = $this->featureService->getFeatures(array($date.' > '=> $from), "$date desc", 1, 10);
        $this->view->listType = $type;
        $this->renderRawView('feature/ajax-feature-list.php');
    }

    /**
     * Used for ordering features within the context of their siblings
     *
     */
    public function orderFeaturesAction()
    {
        $feature = $this->byId();
        $project = null;

        if ($this->_getParam('projectid')) {
            $project = $this->projectService->getProject($this->_getParam('projectid'));
        }
        
        if (!$project && $feature) {
            $project = $this->projectService->getProject($feature->projectid);
        }
        
        if (!$project) {
            $this->flash("Cannot find valid project");
            return;
        }
        
        $features = $this->featureService->getOrderedFeatures($feature, $project);
        $this->view->features = $features;
        $this->renderView('feature/order.php');
    }

    public function deleteAction()
    {
        $feature = $this->byId();
        $this->featureService->deleteFeature($feature);
        $this->redirect('project', 'view', array('id'=>$feature->projectid, '#features'));
    }

    /**
     * Saves the order of the given ids. 
     *
     */
    public function saveOrderAction()
    {
        $ids = $this->_getParam('ids');
        if (!strlen($ids)) {
            $this->flash("Invalid IDs specified");
            $this->renderView('error.php');
        }

        $ids = split(',', $ids);
        if (count($ids)) {
            $feature = null;
            for ($i=0, $c=count($ids); $i < $c; $i++) {
                $feature = $this->projectService->getFeature($ids[$i]);
                /* @var $feature Feature */
                if (!$feature) continue;
                $feature->sortorder = $i;
                $this->log->debug("Setting order for {$feature->title} to {$i}");
                $this->projectService->saveFeature($feature);
            }
            $this->redirect('project', 'view', array('id' => $feature->projectid, '#features'));
        }
    }
    
/**
     * Links an feature to a particular feature
     */
    public function linkFeatureAction()
    {
        $thisFeature = $this->byId();
        $feature = $this->byId($this->_getParam('featureid'), 'Feature');
        $linkType = $this->_getParam('linktype'); 

        if ($thisFeature == null) {
            $this->flash('Invalid feature specified');
            $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        
        if ($thisFeature == null || $feature == null) {
            $this->flash('Invalid Feature specified');
            $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        try {
	        // okay, link the feature TO the feature
            if ($linkType == 'to') {
                $this->itemLinkService->parentChildLink($thisFeature, $feature);
                $this->flash("Linked feature '$thisFeature->title' to feature '$feature->title'");
            } else {
                $this->itemLinkService->parentChildLink($feature, $thisFeature);
    	        $this->flash("Linked feature '$feature->title' to feature '$thisFeature->title'");
            }
        } catch (Exception $e) {
            $this->flash("Failed linking items: ".$e->getMessage());
        }
        $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
    }
    
    /**
     * Delete a link between an feature and a feature
     */
    public function removeFeatureAction()
    {
        $thisFeature = $this->byId();
        $feature = $this->byId($this->_getParam('featureid'), 'Feature');
        $linkType = $this->_getParam('linktype'); 
        
        if ($thisFeature == null) {
            $this->flash('Invalid Feature specified');
            $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        
        if ($thisFeature == null || $feature == null) {
            $this->flash('Invalid Feature specified');
            $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        try {
            if ($linkType == 'to') {
       	        // okay, delete the link from the feature TO the feature
    	        $this->itemLinkService->deleteLinkBetween($feature, $thisFeature);
    	        $this->flash("Removed link between feature $feature->title and feature $thisFeature->title");
            } else {
                $this->itemLinkService->deleteLinkBetween($thisFeature, $feature);
                $this->flash("Removed link between feature $thisFeature->title and feature $feature->title");
            }

	        
        } catch (Exception $e) {
            $this->flash("Failed removing link between items: ".$e->getMessage());
        }
        $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
    }
    
    public function removeTaskAction()
    {
        $thisFeature = $this->byId();
        $task = $this->byId($this->_getParam('otherid'), 'Task');
        $linkType = $this->_getParam('linktype'); 
        
        if ($thisFeature == null) {
            $this->flash('Invalid Feature specified');
            $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        
        if ($task == null) {
            $this->flash('Invalid task specified');
            $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#features'));
            return;
        }
        try {
            if ($linkType == 'to') {
       	        // okay, delete the link from the feature TO the task
    	        $this->itemLinkService->deleteLinkBetween($thisFeature, $task);
    	        $this->flash("Removed link between feature $thisFeature->title and task $task->title");
            } 
        } catch (Exception $e) {
            $this->flash("Failed removing link between items: ".$e->getMessage());
        }
        $this->redirect('feature', 'edit', array('id' => $this->_getParam('id'), '#tasks'));
    }
    
    /**
     * Create a bunch of tasks from the selected features
     */
    public function createTasksAction()
    {
        $ids = $this->_getParam('createfrom');

        foreach ($ids as $id) {
            $feature = $this->byId($id);
            if ($feature) {
                $task = $this->itemLinkService->createTaskFromFeature($feature);
            }
        }

        $this->redirect('project', 'view', array('id'=>$this->_getParam('projectid'), '#tasks'));
    }
}
?>