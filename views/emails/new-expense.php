Hi <?php $this->o($this->user->username) ?>,

<?php $this->o($this->model->username)?> has just added a new expense. 

Please go to the following URL to review the expense.

<?php echo build_url('client', 'view', array('id' => $this->model->clientid, '#expenses'), true) ?>


This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>