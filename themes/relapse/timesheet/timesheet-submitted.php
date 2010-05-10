<script>
	$("#timesheetdialog").simpleDialog("close");
	$("#timesheetdialog").simpleDialog({url: '<?php echo build_url('timesheet','detailedTimesheet', array('taskid' => $this->task->id)); ?>'});
</script>