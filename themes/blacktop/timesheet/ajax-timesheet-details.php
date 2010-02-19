	<form id="task-<?php echo $this->model->id?>-timesheet-form2" action="<?php echo build_url('timesheet', 'addtime', array('taskid'=>$this->model->id))?>" method="post">
     From <input type="text" id="beginning-<?php echo $this->model->id?>" name="start" size="15" />,
     add <input type="text" id="total-<?php echo $this->model->id?>" name="total" size="3" /> hours.
     <input type="submit" value="Add" class="abutton" />
     <?php $this->calendar('beginning-'.$this->model->id) ?>

     <script type="text/javascript">
         $(document).ready(
             function() {
                 var addTaskNoteForm = $('#task-<?php echo $this->model->id?>-timesheet-form2');
                 addTaskNoteForm.ajaxForm(function() {
					$('#task-<?php echo $this->model->id?>-timesheet-detail').html("Loading...");
                 	$('#task-<?php echo $this->model->id?>-timesheet-detail').load('<?php echo build_url('timesheet','detailedTimesheet', array('taskid'=>$this->model->id))?>');
                 });

             }
         );
     </script>
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
    <p><img title="This record is locked and cannot be altered" class="small-icon" src="<?php echo resource('images/lock.png')?>" /></p></td>    
    <?php else: ?>
    <p><a title="Delete Record" onclick="if (!confirm('Are you sure?')) return false; $.get('<?php echo build_url('timesheet', 'deleterecord', array('id' => $record->id))?>', function() {$('#task-<?php echo $record->taskid?>-timesheet-detail').load('<?php echo build_url('timesheet','detailedTimesheet', array('taskid'=>$record->taskid))?>')}); return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a></p></td>    
    <?php endif; ?>

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