
<?php $label = $this->project->ismilestone ? 'Milestone' : 'Project';?>

<div id="projectStatusOverview">
<p>
	Budgeted <span><?php $this->o(sprintf('%.2f', $this->project->budgeted > 0 ? $this->project->budgeted / za()->getConfig('day_length', 8) : 0)); ?></span> days,
	<?php if ($this->project->featureestimate): ?>
	Estimated
	<span><?php $this->o(sprintf('%.2f', $this->project->featureestimate > 0 ? $this->project->featureestimate : 0)); ?></span> days,
	<?php endif ;?>
	Spent <span><?php $this->o(sprintf('%.2f', $this->project->currenttime > 0 ? $this->project->currenttime / za()->getConfig('day_length', 8) : 0)); ?></span> days so far

	<?php if ($this->project->budgeted > 0 && $this->project->currenttime > 0): ?>
	<?php $this->o(sprintf('%.2f', $this->project->currenttime / $this->project->budgeted * 100)) ?>% of budget.
	<?php endif ;?>
</p>
</div>

<div id="overview">
	<div class="dataDetail">
		<div class="wide-form">
		<p>
			<label>Description</label>
			<?php $this->wikiCode($this->project->description) ?>
		</p>
		</div>
		<div class="inner-column">
			<p>
			<label>Start Date</label><?php $this->o(date('l dS M, Y', strtotime($this->project->started))); ?>
			</p>
			<p>
			<label for="actualstarted">Actual Start:</label>
			<?php if ($this->project->actualstart): ?>
			<?php $this->o(date('F jS Y', strtotime($this->project->actualstart))); ?>
			<?php else: ?>
			&nbsp;
			<?php endif; ?>
			</p>
			<p>
			<label>Due Date</label>
			<?php $this->o($this->project->due ? date('l dS M, Y', strtotime($this->project->due)) : '_'); ?>
			</p>
			<?php if ($this->project->url):?>
			<p>
			<label>Website</label>
			<a href="<?php $this->o($this->project->url); ?>"><?php $this->o($this->project->url); ?></a>
			</p>
			<?php endif; ?>
			<!--<p>
			<label>Estimated Days</label><?php $this->o($this->project->estimated > 0 ? $this->project->estimated : 0); ?>
			</p>
			<p>
			<label>... by Tasks</label><?php $this->o($this->project->taskestimate > 0 ? $this->project->taskestimate : 0); ?> days
			</p>-->
			<p>
			<label>Estimated time</label><?php $this->o($this->project->featureestimate > 0 ? $this->project->featureestimate : 0); ?> days
			</p>
			<p>
			<label>Time Spent</label><?php $this->o($this->project->currenttime > 0 ? $this->project->currenttime : 0); ?> hours
			</p>
		</div>
		<div class="inner-column">
			<ul class="largeList">
				<li><?php $this->addToPane(build_url('project', 'milestoneslist', array('projectid' => $this->project->id)), 'Milestones', 'Milestones for '.$this->project->title) ?></li>
				<li><?php $this->dialogPopin('featurelist', "Feature Overview", build_url('feature', 'list', array('projectid'=>$this->project->id)), array('width' => 1000, 'height' => '90%')) ?></li>
				<li><?php $this->addToPane(build_url('issue', 'list', array('projectid' => $this->project->id)), 'Issues', 'Issues for '.$this->project->title) ?></li>
				<li><?php $this->dialogPopin('currenttimes', "Current Timesheet", build_url('timesheet', 'index', array('projectid'=>$this->project->id)), array('width' => 1000)) ?></li>
				<li><?php $this->addToPane(build_url('project', 'statuslist', array('projectid'=>$this->project->id)), 'Status Reports', 'Status reports for '.$this->project->title) ?></li>
				<li><?php $this->addToPane(build_url('project', 'edit', array('id'=> $this->project->id)), 'Edit Project', 'Edit '.$this->project->title, 'RightPane'); ?></li>
				<li><?php $this->addToPane(build_url('project', 'childProjects', array('projectid'=> $this->project->id)), 'Child Projects'); ?></li>
			</ul>
		</div>
		 <div style="clear: left;"></div>
		<div>
		<p>
			
		<!-- <a class="abutton" title="View Traceability" href="<?php echo build_url('project', 'traceability', array('id'=> $this->project->id))?>">
			Trace the <?php echo $label ?>
		</a> -->
		</p>
			<div>
				<form action="<?php echo build_url('project', 'addChild')?>" method="post">
					<input type="hidden" name="projectid" value="<?php echo $this->project->id?>" />
					<input size="40" type="text" name="newTitle" value="Sub project of <?php $this->o($this->project->title)?>" />
					<input type="submit" class="abutton" value="Create Sub Project" />
				</form>
			</div>
		</div>

<!--
		<h3>Current Overview</h3>
		<?php
			/*$projectOverview = new CompositeView('project/project-overview.php');
			$projectOverview->project = $this->project;
			echo $projectOverview->render('null');*/
		?>
 -->
	</div>
	<div style="clear: left;"></div>
</div>