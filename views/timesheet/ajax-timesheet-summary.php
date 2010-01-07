<table>
<thead>
<tr>
    <th width="25%">Owner</th>
    <th width="35%">Title</th>
    <th width="20%">Estimate</th>
    <th width="20%">Timespent</th>
</tr>
</thead>
<tbody>
<?php 
$total = 0;
foreach ($this->tasks as $task): 
    $total += $task->timespent;
?>
<tr class="task-<?php echo $task->complete ? 'complete' : 'incomplete'?>">
    <td><p><?php $this->o($task->username)?></p></td>
    <td><p><?php $this->o($task->title)?></p></td>
    <td><p><?php $this->o($task->estimated)?></p></td>
    <td><p><?php $this->o($task->getDuration())?></p></td>
</tr>
<?php endforeach;?>
<tr>
    <td></td>
    <td></td>
    <td>Total</td>
    <td><?php 
            $days = gmdate("d", $total) - 1;
			$hours = gmdate("H", $total);
			$hours = $hours + $days * 24;
			$mins = gmdate("i", $total);
			echo $hours.':'.$mins;?></td>
</tr>
</tbody>
</table>