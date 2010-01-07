<ul>
<?php foreach ($this->timesheets as $timesheet): ?>
<li>

<a class="action-icon" title="Edit" href="<?php echo build_url('timesheet', 'edit', array('id'=>$timesheet->id, 'clientid' => $timesheet->clientid ? $timesheet->clientid : 0, 'projectid'=> $timesheet->projectid ? $timesheet->projectid : 0))?>">
<img class="small-icon" src="<?php echo resource('images/pencil.png');?>" />
</a>
<?php if (!$timesheet->locked): ?>
<a class="action-icon" title="Delete Timesheet" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('timesheet', 'delete', array('id' => $timesheet->id))?>'; return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
<?php endif; ?>
<a class="action-icon" title="View" href="<?php echo build_url('timesheet', 'view', array('id' => $timesheet->id));?>"><img class="small-icon" src="<?php echo resource('images/eye.png');?>" /></a>
<?php $this->o($timesheet->title.': '.date('Y-m-d', strtotime($timesheet->from)).' - '.date('Y-m-d', strtotime($timesheet->to)));?>
<?php if ($timesheet->locked): ?>
<img title="This timesheet is locked" class="small-icon" src="<?php echo resource('images/lock.png')?>" />
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>