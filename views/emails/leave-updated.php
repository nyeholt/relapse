Hi <?php $this->o($this->user->username) ?>,

Your leave application for <?php echo date('Y-m-d', strtotime($this->model->from)) ?> to <?php echo date('Y-m-d', strtotime($this->model->to)) ?> has been <?php echo $this->model->status?>.

This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>