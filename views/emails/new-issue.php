Hi <?php $this->o($this->user->username) ?>,

A new request has been created in a project you are
assigned to, "<?php $this->o($this->project->title)?>".

The request can be viewed at <?php echo user_url($this->user, 'issue', 'edit', array('id'=>$this->model->id), true) ?>


The project can be viewed at <?php echo user_url($this->user, 'project', 'view', array('id'=>$this->project->id), true) ?>


-----
Summary: <?php $this->o($this->model->title);?>


Severity: <?php $this->o($this->model->severity)?> 
Type: <?php $this->o($this->model->issuetype)?>

Description:
<?php $this->o($this->model->description, false) ?>

-----

This is an automatically generated email from <?php echo user_url($this->user, 'index', 'index', null, true) ?>