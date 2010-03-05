<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_ProjectSelector extends NovemberHelper
{
	/**
	 * Builds a project selection list control for a given client. 
	 *
	 * @param array $projects
	 * @param String $select What kind of projects can be selected? project|milestone|any 
	 */
	public function ProjectSelector($forField, $projects, $select = 'any', $empty = false, $default=null, $showmilestones=true)
	{
		// Build up the HTML cause we'll try and cache this at a later point
		// based on the clientid
		$value = isset($this->view->model->$forField) ? $this->view->model->$forField : null;
		
		if ($value == null && $default) {
		    $value = $default;
		}
		
        $html = '<span id="projectSelector-'.$forField.'"><select class="input" name="'.$forField.'" id="'.$forField.'">';
        if ($empty) {
        	$html  .= '<option value=""> </option>';
        }

        foreach ($projects as $project) {
        	/* @var $project Project */
			$html .= $this->iterateSubProjects($project, $select, $value,0,$showmilestones);
        }
        $html .= '</select></span>';
        echo $html;
	}
	
	private function iterateSubProjects(Project $project, $select, $value, $level=0,$showmilestones=true)
	{
		
		$selected = $value == $project->id ? 'selected="selected"' : ''; 
		
		$disabled = '';
		if ($project->ismilestone) {
			if (!$showmilestones) {
				return "";
			}
			$disabled = $select == 'project' ? 'disabled="disabled"' : '';
		} else {
			$disabled = $select == 'milestone' ? 'disabled="disabled"' : '';
		}
		$prepend = '';
		for ($i = 0; $i < $level; $i++) {
			$prepend .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		if ($project->id == $this->view->model->id) {
			$disabled = 'disabled="disabled"';
		}

		$html = '<option value="'.$project->id.'" '.$disabled.' '.$selected.'>'.$prepend.$this->view->escape($project->title)." (".$project->id.")".'</option>';
		
		$children = $project->getChildProjects();
		foreach ($children as $childProject) {
			$html .= "\n".$this->iterateSubProjects($childProject, $select, $value, $level + 1);
		}
		
		return $html;
	}
}

?>