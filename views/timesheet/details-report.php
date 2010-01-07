<h2>
Reports for <?php $this->o($this->reportingOn); ?>
</h2>

<table>
<thead>
<tr>
    <th width="15%">User</th>
    <th width="35%">Task</th>
    <th width="15%">Start</th>
    <th width="15%">End</th>
    <th width="15%">Time Spent</th>
</tr>
</thead>
<tbody>
<?php 
$total = 0;
foreach ($this->records as $record): 
    $total += $record->endtime - $record->starttime;
?>
<tr>
    <td><p><?php $this->o($record->userid)?></p></td>
    <td><p><?php $this->o($record->getTaskTitle())?></p></td>
    <td><p><?php echo date('H:i d/m', $record->starttime)?></p></td>
    <td><p><?php echo date('H:i d/m', $record->endtime)?></p></td>
    <td><p><?php echo $record->getDuration() ?></p></td>
</tr>
<?php endforeach;?>
<tr>
    <td></td>
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