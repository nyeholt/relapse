<script type="text/javascript">
	$('#newLinkedTask').simpleDialog('close');
	Relapse.createDialog('taskdialog', {title: 'Edit task', modal: false, url: '<?php echo build_url('task', 'edit', array('id'=>$this->model->id))?>'});
</script>