<script type="text/javascript">
	$('#newFeatureTask').simpleDialog('close');
	
	if ($('#dialogdiv').length == 0) {
		$('body').append('<div class="std dialog" id="dialogdiv"></div>');
	}
	$('#dialogdiv').simpleDialog({title: 'Edit task', modal: false, onClose: function () { window.location.reload(false) }, url: '<?php echo build_url('task', 'edit', array('id'=>$this->model->id))?>'});
</script>