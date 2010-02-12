
<ul class="largeDualList">
	<li><a href="#" onclick="javascript: $('#dialogdiv').simpleDialog({title: 'Edit task', modal: false, url: '<?php echo build_url('task', 'edit', array('id' => $this->model->id)) ?>'}); return false; ">Edit this task</a></li>
	<li><a href="">Manage timesheet</a></li>
</ul>