Timesheet Summary Report
<?php print("\r\n");?>
From,<?php $this->o($this->startDate);print("\r\n");?>
To,<?php $this->o($this->endDate);print("\r\n"); ?><?php if ($this->user): ?>
User,<?php $this->o($this->user->username);print("\r\n");?>
<?php endif; ?><?php if ($this->client): ?>
Client,<?php $this->o($this->client->title);print("\r\n");?>
<?php endif; ?><?php if ($this->project): ?>
Project,<?php $this->o($this->project->title);?> (<?php $this->o($this->project->id);print("\r\n");?>)
<?php endif; ?><?php print("\r\n");?>
Category,TotalHours,TotalDays,Percent
<?php
	$totalHours = 0;
	$totalDays = 0;
	$totalPercent = 0;
	foreach ($this->timeByCategory as $cat => $seconds) {
		$totalHours+=$this->elapsedTime($seconds);
		$totalDays+=$this->hoursAsDays($this->elapsedTime($seconds));
	}
	foreach ($this->timeByCategory as $cat => $seconds) {
		$hours =  $this->elapsedTime($seconds);
		$days = $this->hoursAsDays($this->elapsedTime($seconds));
		$percent = (100 / $totalHours) * $hours;
		$totalPercent+=$percent;
		
		print(($cat?$cat:"Unknown").",");
		print(round($hours,2).",");
		print(round($days,2).",");
		print(round($percent,2).'%');
		print("\r\n");
	}?>
TOTAL,<?php print(round($totalHours,2));?>,<?php print(round($totalDays,2));?>,<?php print(round($totalPercent,2).'%');?>
