<?php

/**
 * Portal to accessing features in a system
 */
class FeatureService
{
    /**
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * The item linking service
     * @var ItemLinkService
     */
    public $itemLinkService;
    
    /**
     * 
     *
     * @var TrackerService
     */
    public $trackerService;
    
    /**
     * Get a list of issues.
     *
     * @return unknown
     */
    public function getOrderedFeatures($parent = null, $project = null)
    {
        if ($parent == null) {
            $where = array();
            if ($project) {
                $where = array('projectid=' => $project->id);
            }
            return $this->itemLinkService->getOrphanItems('Feature', $where, 'sortorder asc');
        } else {
            return $this->itemLinkService->getLinkedItemsOfType($parent, 'from', 'Feature', array(), 'sortorder asc');
        }
    }

	/**
	 * Get features with a particular where clause
	 *
	 * @param array $where
	 * @return ArrayObject
	 */
    public function getFeatures($where = array())
    {
        return $this->dbService->getObjects('Feature', $where);
    }
    
    /**
     * Get all the features in a project, in their hierarchical tree structure
     *
     * @param Project $project
     * @param unknown_type $where
     * @return ArrayObject
     */
    public function getProjectFeatures(Project $project, $where=array())
    {
        $features = $this->itemLinkService->getOrphanItems('Feature', array('projectid=' => $project->id), 'sortorder asc');
        
        // Get all the milestones that features have. This is preloaded so we can easily bring thevalues
		// across
		$milestones = $project->getMilestones();
		$mapping = array();
		if (count($milestones)) {
			foreach ($milestones as $milestone) {
				$mapping[$milestone->id] = $milestone->title;
			}
		}
		

        foreach ($features as $feature) {
            // get all the features linked to this feature
            $this->loadChildFeatures($feature, $mapping);
        }

        return $features;
    }

    /**
     * Load all the child features of a given feature
     *
     * @param unknown_type $feature
     */
    private function loadChildFeatures($feature, $milestoneMapping)
    {
    	if (isset($milestoneMapping[$feature->milestone])) {
    		$feature->setMilestoneTitle($milestoneMapping[$feature->milestone]);
    	}
        $children = $this->itemLinkService->getLinkedItemsOfType($feature, 'from', 'Feature', array(), 'sortorder asc');
        $feature->setChildFeatures($children);
        foreach ($children as $child) {
            $this->loadChildFeatures($child, $milestoneMapping);
        }
    }
    
    /**
     * Modifies the possible children array by moving features from out of
     * that array into 
     */
    private function addChildrenToFeature(Feature $parent, $possibleChildren)
    {
        $toRemove = array();
        foreach ($possibleChildren as $key => $child) {
            // A feature is a child if its parentpath == parent->parentpath + parent id
            if ($child->parentpath == $parent->parentpath.'-'.$parent->id) {
                $parent->addChild($child);
                $toRemove[] = $key;
                // let this feature process the children too
                $removeMore = $this->addChildrenToFeature($child, $possibleChildren);
                $toRemove = array_merge($toRemove, $removeMore);
            }
        }

        return $toRemove;
    }
    
    /**
     * Get the child Features of the passed in Feature
     * @param Feature $feature
     * @return ArrayObject
     */
    public function getChildFeatures(Feature $feature) 
    {
        return array();
        $features = $this->getFeatures(array('parentpath=' => $feature->parentpath.'-'.$feature->id));
        return $features;
    }
    
    /**
	 * Saves an existing feature
	 *
	 * @param Feature $feature
	 */
    public function saveFeature(Feature $feature)
    {
    	$this->trackerService->track('save-feature', $feature->id);
        $this->dbService->saveObject($feature);
    }

    /**
     * Delete a feature and all sub features.
     */
    public function deleteFeature(Feature $feature)
    {
        $this->dbService->beginTransaction();
        $childFeatures = $this->getChildFeatures($feature);
        foreach ($childFeatures as $child) {
            $this->deleteFeature($child);
        }
        $this->dbService->delete($feature);
        $this->dbService->commit();
        
    }
}
?>