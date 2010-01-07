Hi <?php $this->o($this->user->username) ?>,

A new code review has been created in a project you are
assigned to, "<?php $this->o($this->project->title)?>".

The review can be viewed at <?php echo user_url($this->user, 'codereview', 'view', array('id'=>$this->model->id), true) ?>


The project can be viewed at <?php echo user_url($this->user, 'project', 'view', array('id'=>$this->project->id), true) ?>


-----
Summary: <?php $this->o($this->model->title);?>

Description:
<?php $this->o($this->model->description, false) ?>

-----

This is an automatically generated email from <?php echo user_url($this->user, 'index', 'index', null, true) ?>