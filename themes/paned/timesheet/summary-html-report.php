<div class="std">
<h3>Timesheet Summary Report</h3>
	<p>
	<a href="<?php echo build_url('timesheet', 'summaryReportExport', $this->params)?>" class="abutton">Export To CSV</a>
	</p>
	<ul>
		<li>From: <?php $this->o($this->startDate)?></li>
		<li>To: <?php $this->o($this->endDate); ?></li>
	<?php if ($this->user): ?>
		<li>User: <?php $this->o($this->user->username);?></li>
	<?php endif; ?>
	<?php if ($this->client): ?>
		<li>Client: <?php $this->o($this->client->title);?></li>
	<?php endif; ?>
	<?php if ($this->project): ?>
		<li>Project: <?php $this->o($this->project->title);?> (<?php $this->o($this->project->id);?>)</li>
	<?php endif; ?>
	</ul>
	<table class="item-table" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
	<th>Category</th><th>TotalHours</th><th>TotalDays</th><th>Percent</th>
	</tr>
	</thead>
	<tbody>
	<?php
		$totalHours = 0;
		$totalDays = 0;
		$totalPercent = 0;
		foreach ($this->timeByCategory as $cat => $seconds) {
			$totalHours+=$this->elapsedTime($seconds);
			$totalDays+=$this->hoursAsDays($this->elapsedTime($seconds));
		}
		foreach ($this->timeByCategory as $cat => $seconds) {
			$hours = $this->elapsedTime($seconds);
			$days = $this->hoursAsDays($this->elapsedTime($seconds));
			$percent = (100 / $totalHours) * $hours;
			$totalPercent+=$percent;
			$this->params['category'] = $cat;
			print("<tr>\n");
			print("<td><a href=\"".(build_url('timesheet', 'index', $this->params))."\">".($cat?$cat:"Unknown")."</a></td>\n");
			print("<td>".round($hours,2)."</td>\n");
			print("<td>".round($days,2)."</td>\n");
			print("<td>".round($percent,2).'%'."</td>\n");
			print("</tr>\n");
		}?>
	<tr>
	<td>TOTAL</td>
	<td><?php $this->o(round($totalHours,2));?></td>
	<td><?php $this->o(round($totalDays,2));?></td>
	<td><?php $this->o(round($totalPercent,2).'%');?></td>
	</tr>
	<tbody>
	</table>
</div>