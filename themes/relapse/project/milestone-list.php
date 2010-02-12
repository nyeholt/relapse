<div class="std">
	<?php if (!$this->project->ismilestone): ?>
	<h3>Milestones</h3>
	<?php endif; ?>

	<!--<p>
	Show tasks for 
	<select name="projectuser" id="projusersel">
		<option value="all">All</option>
	<?php
	$pid = $this->projectuser ? $this->projectuser->id : 0;
	?>
	<?php foreach ($this->project->getUsers() as $uid => $projectUser): ?>
		<option value="<?php echo $uid?>" <?php echo $pid == $uid ? "selected='selected'" : ''?>><?php $this->o($projectUser->username)?></option>
	<?php endforeach; ?>
	</select>
	<script type="text/javascript">
		$().ready(function() {
			$('#projusersel').change(function() {
				location.href = '<?php echo build_url('project', 'view', array('id'=>$this->project->id))?>projectuser/'+$(this).val();
			});
		});
	</script>
	</p>-->

	<?php if ($this->project->ismilestone): ?>
		<?php $totalComplete = $this->project->countTasks(1);
		$totalTasks = $totalComplete + $this->project->countTasks(0);
		$percentageComplete = 0;
		if ($totalComplete != 0 && $totalTasks != 0) {
			$percentageComplete = ceil($totalComplete / $totalTasks * 100);
		}
		?>
		<div class="milestone-entry">
		<?php $this->percentageBar($percentageComplete, 2, '#0045FF')?>

		<h3>Tasks in this milestone (due <?php $this->o(date('F jS Y', strtotime($this->project->due)))?>, <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> tasks completed)</h3>
		<ul class="project-task-summary">
		<?php
		$completed = 0;
		$estimated = 0;
		foreach ($this->project->getOpenTasks($this->projectuser) as $openTask):
			$completed += $openTask->timespent;
			$estimated += $openTask->estimated;
		?>
			<li>
			<?php $this->percentageBar($openTask->getPercentage())?>
			<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
			<?php foreach ($openTask->userid as $username): ?>
			<span>[<?php $this->o($username)?>]</span>
			<?php endforeach; ?>
			<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
			</li>
		<?php endforeach; ?>
		</ul><br/>

		<p>Time spent on open tasks: <?php $this->o(sprintf('%.2f', ($completed > 0 ? $completed / 3600 : 0))) ?> / <?php $this->o($estimated) ?></p>
		</div>
	<?php else: // not a milestone ?>
		<?php $children = $this->project->getMilestones();
		foreach ($children as $childProject): ?>
			<?php $totalComplete = $childProject->countContainedTasks(1);
			$totalTasks = $totalComplete + $childProject->countContainedTasks(0);
			$percentageComplete = 0;
			if ($totalComplete != 0 && $totalTasks != 0) {
				$percentageComplete = ceil($totalComplete / $totalTasks * 100);
			}
			?>
			<div class="milestone-entry bordered">
			<?php $this->percentageBar($percentageComplete, 2, '#0045FF')?>
			<h3><?php $this->o($childProject->title)?> (due <?php $this->o(date('F jS Y', strtotime($childProject->due)))?>, <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> completed)
			<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
			<a href="<?php echo build_url('task', 'edit', array('projectid'=>$childProject->id))?>"><img src="<?php echo resource('images/add.png'); ?>" /></a>
			<?php endif; ?>
			<a href="<?php echo build_url('project', 'view', array('id'=>$childProject->id))?>"><img src="<?php echo resource('images/eye.png'); ?>" /></a>
			</h3>

			<p>
			<?php $this->bbCode($childProject->description) ?>
			</p>

			<div>
			<h4><a href="#" onclick="$('.milestone-feature-listing').toggle(); return false;">Features</a></h4>
			<div class="milestone-feature-listing">
				<?php $featureEstimate = 0; ?>
				<ul>
					<?php foreach ($childProject->getFeatures() as $feature): ?>
					<li>
					<?php
					$percentageComplete = $feature->getPercentageComplete();
					$featureEstimate += $feature->estimated;
					?>
					<?php $this->percentageBar($percentageComplete)?>
					(<?php $this->o($feature->estimated); ?>)
					<?php if ($feature->complete): ?>
						<img class="small-icon" src="<?php echo resource('images/accept.png')?>" />
					<?php endif;?>
					<a href="<?php echo build_url('feature', 'edit', array('id' => $feature->id))?>"><?php $this->o($feature->title)?></a>
					</li>
					<?php endforeach; ?>
				</ul>
				<p>Estimated <?php $this->o($featureEstimate) ?> days</p>
			</div>
			</div>

			
			<h4 class="top-margin"><a href="#" onclick="$('.project-task-summary').toggle(); return false;">Tasks</a></h4>
			
			<ul class="project-task-summary" style="display:none;">
			<?php
			$completed = 0;
			$estimated = 0;
			foreach ($childProject->getContainedOpenTasks($this->projectuser) as $openTask):
				$completed += $openTask->timespent;
				$estimated += $openTask->estimated;
			?>
				<li>
				<?php $this->percentageBar($openTask->getPercentage())?>
				<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
				<?php foreach ($openTask->userid as $username): ?>
				<span>[<?php $this->o($username)?>]</span>
				<?php endforeach; ?>
				<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
				</li>
			<?php endforeach; ?>
			</ul><br/>
			
			<p>Time spent on open tasks: <?php $this->o(sprintf('%.2f', ($completed > 0 ? $completed / 3600 : 0))) ?> / <?php $this->o($estimated) ?></p>
			</div>
		<?php endforeach; ?>

		<!-- Only allowing projects to have milestones -->
		<form action="<?php echo build_url('project', 'addChild')?>" method="post">
			<input type="hidden" name="projectid" value="<?php echo $this->project->id?>" />
			<input type="hidden" name="milestone" value="1" />
			New Milestone:
			<input size="40" type="text" name="newTitle" value="" />
			Due:
			<input readonly="readonly" type="text" name="due" id="due" value="" size="10" />
			<?php $this->calendar('due', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
			<input type="submit" class="abutton" value="Create Milestone" />
		</form>
	<?php endif; ?>
</div>