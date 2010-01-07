<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_TaskSummary extends NovemberHelper
{
	public function TaskSummary($project)
	{
$totalComplete = $project->countContainedTasks(1);
$totalTasks = $totalComplete + $project->countContainedTasks(0);
?>

<h3><?php $this->view->o($project->title)?> (<?php $this->view->o($totalComplete)?> of <?php $this->view->o($totalTasks)?> completed)  <a href="<?php echo build_url('project', 'view', array('id'=>$project->id))?>">&raquo;</a></h3>
<ul class="project-task-summary">
<?php foreach ($project->getContainedOpenTasks() as $openTask): ?>
	<li>
	<div style="float: right; width: 100px; background-color: #AF0A30; height: 10px;">
	<div style="height: 10px; background-color: #00CF1C; width: <?php echo $openTask->getPercentage() > 100 ? 100 : $openTask->getPercentage()?>px;"></div>
	</div>
	<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
	<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->view->o($openTask->title)?></a>
	</li>
<?php endforeach; ?> 
</ul><br/>
		<?php 
	}
}

?>