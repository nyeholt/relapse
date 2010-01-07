Hi <?php $this->o($this->user->username) ?>,

You have been assigned a new task "<?php $this->o($this->model->title);?>".

The task may be viewed at <?php echo build_url('task', 'edit', array('id'=>$this->model->id), true) ?>

-----

<?php $this->o($this->model->description, false) ?>

-----

This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>