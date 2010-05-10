<form id="linkedtaskform" method="post" action="<?php echo build_url('task', 'newtask')?>" class="data-form ajaxForm replacecontent">
	<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
	<input type="hidden" name="_ajax" value="1" />
	<input type="hidden" name="type" value="<?php echo get_class($this->model) ?>" />
	<p>
		<label for="tasktitle">Add New Task</label>
		<input class="required" type="text" id="tasktitle" name="tasktitle" />
	</p>
	<?php if ($this->projects): ?>
	<p>In Milestone</p>
	<p>
	<?php $this->projectSelector('newtaskProjectid', $this->projects, 'milestone', false, $this->defaultmilestone) ?>
	</p>
	<?php endif; ?>
	<p>
	<input type="submit" value="Create Task" class="abutton" />
	</p>
</form>