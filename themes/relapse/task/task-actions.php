<?php if ($this->new): ?>
<script type="text/javascript">$('#dialogdiv').simpleDialog('close'); window.location.reload(false);</script>
<?php else: ?>
<script type="text/javascript">
	$('#dialogdiv').simpleDialog('close');
	$('#dialogdiv').simpleDialog({title: 'Edit task', modal: false, url: '<?php echo build_url('task', 'edit', array('id'=>$this->model->id))?>'});
</script>
<?php endif; ?>