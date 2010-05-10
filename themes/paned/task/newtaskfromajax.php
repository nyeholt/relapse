<script type="text/javascript">
	Relapse.closeDialog(null, $('#linkedtaskform'));
	Relapse.addToPane('RightPane', '<?php echo build_url('task', 'edit', array('id'=>$this->model->id))?>', '<?php $this->o('Edit task ' . $this->model->title)?>');
</script>