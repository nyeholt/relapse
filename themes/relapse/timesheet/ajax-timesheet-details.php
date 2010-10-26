<form class="ajaxForm" id="task-timesheet-form" action="<?php echo build_url('timesheet', 'addtime', array('taskid'=>$this->task->id))?>" method="post">
	<input type="hidden" name="_ajax" value="1" />
     From <input type="text" id="task-time-beginning" name="start" size="15" />

	 <select name="start-hour">
		 <?php for ($i = 0; $i < 24; $i++) {
			 $selected = $i == 12 ? ' selected="selected"' : '';
			?><option value="<?php echo $i ?>" <?php echo $selected ?>><?php echo $i ?></option><?php
		 }?>
	 </select>

	 <select name="start-min">
		 <option value="00">00</option>
		 <option value="15">15</option>
		 <option value="30">30</option>
		 <option value="45">45</option>
	 </select>

     add <input type="text" id="total-<?php echo $this->task->id?>" name="total" size="3" /> hours.
     <input type="submit" value="Add" class="abutton" />
     <?php $obj->showTime = true; $this->calendar('task-time-beginning', $obj) ?>
	 <?php $this->timePicker('task-time-start'); ?>
</form>

<table class="item-table">
<thead>
<tr>
    <th width="25%">User</th>
    <th width="25%">Start</th>
    <th width="25%">End</th>
    <th width="15%">Duration</th>
    <th width="10%"></th>
</tr>
</thead>
<tbody>
<?php 
$total = 0;
foreach ($this->records as $record): 
    $total += $record->endtime - $record->starttime;
?>
<tr valign="middle">
    <td><p><?php $this->o($record->userid)?></p></td>
    <td><p><?php echo date('H:i d/m', $record->starttime)?></p></td>
    <td><p><?php echo date('H:i d/m', $record->endtime)?></p></td>
    <td><p><?php echo $record->getDuration() ?></p></td>
    <td>
    <?php if ($record->timesheetid): ?>
    <p><img title="This record is locked and cannot be altered" class="small-icon" src="<?php echo resource('images/lock.png')?>" /></p> 
    <?php else: ?>
    <p><a title="Delete Record" onclick="Relapse.Tasks.deleteTimesheetRecord(<?php echo $record->id ?>, this); return false; " href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a></p>
    <?php endif; ?>
	</td>  
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
	<td></td>
</tr>
</tbody>
</table>