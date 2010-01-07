
<div id="display-status">

<h2>Current features and work effort (generated at <?php $this->o($this->u()->wordedDate($this->status->dategenerated))?>)</h2>

<p>Note that this is an indicator of all the work on these features up until this point in time. The 
time listing down below indicates how much time has been spent by people during the given date period. 
</p>

<?php if ($this->status->snapshot->features != null): ?>
	<ul>
	<?php 
	// total estimate
	$featureEstimate = 0;
	
	// what estimated time is done
	$featureComplete = 0;
	
	$taskTimeSpent = 0;
	
	// how much time was spent on features that are complete
	$completedTime = 0;
	
	foreach ($this->status->snapshot->features as $feature): ?>
		<li>
		<?php 
		$percentageComplete = $feature->getPercentageComplete();
		$featureEstimate += $feature->estimated;
		if ($feature->complete) {
			$featureComplete += $feature->estimated;
		}
		?>
		<?php $this->percentageBar($percentageComplete, 1.5)?>
		(<?php $this->o($feature->estimated); ?>  est days)
		
		
		<a href="<?php echo build_url('feature', 'edit', array('id' => $feature->id))?>"><?php $this->o($feature->title)?></a>
		<?php if ($feature->complete): ?>
		<img class="small-icon" src="<?php echo resource('images/accept.png')?>"></img>
		<?php endif;?>
		
		<?php if ($feature->tasks != null && count($feature->tasks)): ?>
			<ul>
			<?php foreach ($feature->tasks as $task): 
				$taskTimeSpent += $task->timespent;
				if ($feature->complete) {
					$completedTime += $task->timespent;
				}
			?>
			<li>
			<?php $this->percentageBar($task->getPercentage())?>
			(<?php $this->o(sprintf('%.1f', $task->estimated > 0 ? $task->estimated / za()->getConfig('day_length', 8) : 0)) ?> est days,
			<?php $this->o(sprintf('%.2f', $task->timespent > 0 ? $task->timespent / 3600 : 0)) ?> hours spent)
			<a href="<?php echo build_url('task', 'edit', array('id' => $task->id))?>"><?php $this->o($task->title)?></a>
			<?php if ($task->complete): ?>
			<img class="small-icon" src="<?php echo resource('images/accept.png')?>"></img>
			<?php endif;?>
			</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php 
	$timeInHours = $taskTimeSpent > 0 ? $taskTimeSpent / 3600 : 0;
	$completeTimeInHours = $completedTime > 0 ? $completedTime / 3600 : 0;
	$workPercentage = 0; 
	if ($featureComplete && $featureEstimate) {
		$workPercentage = $featureComplete / $featureEstimate * 100;
	}
	
	
	?>
	<table cellpadding="5">
		<tr>
			<th></th>
			<th>Estimated Total</th>
			<th>Estimated Complete</th>
			<th>Total time spent</th>
			<th>Time on completed features</th>
		</tr>
		<tr>
			<td><strong>Days</strong></td>
			<td><?php $this->o(sprintf('%.2f', $featureEstimate))?></td>
			<td><?php $this->o(sprintf('%.2f', $featureComplete))?></td>
			<td><?php $this->o(sprintf('%.2f', $timeInHours / za()->getConfig('day_length', 8)))?></td>
			<td><?php $this->o(sprintf('%.2f', $completeTimeInHours / za()->getConfig('day_length', 8)))?></td>
		</tr>
		<tr>
			<td><strong>Hours</strong></td>
			<td><?php $this->o(sprintf('%.2f', $featureEstimate * za()->getConfig('day_length', 8)))?></td>
			<td><?php $this->o(sprintf('%.2f', $featureComplete * za()->getConfig('day_length', 8)))?></td>
			<td><?php $this->o(sprintf('%.2f', $timeInHours))?></td>
			<td><?php $this->o(sprintf('%.2f', $completeTimeInHours))?></td>
		</tr>
		<tr>
			<td><strong>Velocity Factor</strong></td>
			<td colspan="4"><?php $this->percentageBar($workPercentage, 2); ?></td>
		</tr>
		<tr>
			<td><strong>Work Factor</strong></td>
			<td colspan="4"><?php $this->percentageBar(($completeTimeInHours / ($featureComplete * za()->getConfig('day_length', 8)) * 100)  , 2); ?></td>
		</tr>
	</table>
<?php else: ?>

<?php endif; ?>

<h2>User time breakdown for the period <?php echo $this->u()->wordedDate($this->status->startdate) ?> to
<?php echo $this->u()->wordedDate($this->status->enddate) ?></h2>
<table class="item-table">
	<thead>
		<tr>
			<th>User</th>
			<th>Time Spent</th>
		</tr>
	</thead>
	<tbody>
<?php 
	$totalSpent = 0;
	foreach ($this->status->getRecordedTime() as $user => $time): 
	$totalSpent += $time; ?>
	<tr>
		<td><?php $this->o($user); ?>
		</td>
		<td align="center"><?php $this->o(sprintf('%.2f', $time > 0 ? $time / 3600 : 0)); ?> hours
		</td>
	</tr>
<?php endforeach; ?>
	<tr>
		<td></td>
		<td><?php $this->o(sprintf('%.2f', $totalSpent > 0 ? $totalSpent / 3600 : 0)); ?> hours in this week</td>
	</tr>
	</tbody>
</table>
</div>