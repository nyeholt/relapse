<script>
	$('.pReload').click();
	Relapse.closeDialog(null, $("#task-timesheet-form"));
	Relapse.addToPane('RightPane', '<?php echo build_url('timesheet','detailedTimesheet', array('taskid' => $this->task->id)); ?>', '<?php $this->o('Timesheet for '.$this->task->title)?>');
</script>