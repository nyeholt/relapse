$('#record-id').val('<?php echo $this->record->id?>');
$('#endtime').val('<?php echo $this->record->endtime ?>');
$('#task-time').html('<?php echo $this->task->getDuration(); ?>');
setCompletion(<?php echo $this->task->getPercentage()?>);

<?php if ($this->task->complete): ?>
	window.clearTimeout(updateTimeout);
	alert("This task is already complete");
<?php endif; ?>