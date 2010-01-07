<?php 
$startTime = null;
$startDate = null;
$endTime = null;
$endDate = null;

$c = count($this->tasks);
if ($c > 0) {
    $startTime = strtotime($this->tasks[0]->startdate);
    $startDate = $this->tasks[0]->startdate;
    foreach ($this->tasks as $task) {
        if (strtotime($task->due) > $endTime) {
            $endTime = strtotime($task->due);
            $endDate = $task->due;
        }
    }
} 
$endTime = $endTime += 86399;


?>
<div id="parent-links">
    <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#tasks'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>
<h2>Chart of <?php $this->o($this->project->title)?></h2>
<div id="task-chart" style="width: 100%; height: 400px; overflow: auto">
<table cellpadding="0" cellspacing="0" class="task-chart">
<thead>
	<tr>
		<th></th>
	<?php 
	$start = true;
	for ($i = $startTime; $i <= $endTime; $i += 86400) {
		if (date('d', $i) == 1 || $start) {
            echo '<th style="font-size: x-small">'.date('M', $i).'</th>';
            $start = false;
		} else {
		    echo '<th>&nbsp;</th>';
		}
	}
	?>
	</tr>
	<tr>
		<th>Title</th>
	<?php for ($i = $startTime; $i <= $endTime; $i += 86400): ?>
		<th><?php echo date('d', $i)?></th>
	<?php endfor; ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($this->tasks as $task): ?>
	<tr>
	<td style="white-space: nowrap">
		<a title="<?php $this->o($task->title)?>" href="<?php echo build_url('task', 'edit', array('id'=>$task->id))?>"><?php $this->o($this->ellipsis($task->title, 32))?></a>
	</td>
		<?php 
		$dayNumber = 0;
		$percentComplete = $task->getPercentage();
		$taskStart = strtotime($task->startdate);
		$taskEnd = strtotime($task->due) + 86399;
        $days = $taskEnd - $taskStart;
		if ($days > 0) {
		    $days = ceil($days / 86400);
		} else {
		    $days = 1;
		}
		
		for ($i = $startTime; $i <= $endTime; $i += 86400) {
			if ($taskStart <= $i && $taskEnd >= $i) {
			    // what day is today as a percentage of the length of time?
			    ++$dayNumber;
			    $percentDay = $dayNumber / $days * 100;
			    $class = '';
			    if ($task->complete || $percentComplete >= $percentDay) {
			        $class = 'completed-bar';
			    }

			    echo '<td title="'; $this->o($task->title); echo '" class="task-bar '.$class.'"><span >&nbsp;</span></td>';
			} else {
			    echo '<td></td>';
			}
			
        }
        $days = 0;
		$dayNumber = 0;
        ?>
	</tr>
	<?php endforeach; ?>
</tbody>
</table>
</div>