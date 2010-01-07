Hi <?php $this->o($this->user->username) ?>,

The project "<?php $this->o($this->model->title)?>" has just had its due date updated to <?php echo date('l jS \of F Y') ?>
by <?php $this->o($this->u()->username)?>

This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>