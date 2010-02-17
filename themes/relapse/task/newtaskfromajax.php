<script type="text/javascript">
	Relapse.createDialog('taskdialog', {title: 'Edit task', modal: false, onClose: function () { window.location.reload(false) }, url: '<?php echo build_url('task', 'edit', array('id'=>$this->model->id))?>'});
</script>