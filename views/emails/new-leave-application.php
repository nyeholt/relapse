Hi <?php $this->o($this->user->username) ?>,

<?php $this->o($this->model->username)?> has just applied for leave. 

Please go to the following URL to review all leave applications

<?php echo build_url('leave', 'list', null, true) ?>


This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>