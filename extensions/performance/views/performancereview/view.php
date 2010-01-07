<h1><?php $this->o($this->model->title)?></h1>
<h4>Review for <?php $this->o($this->user->firstname.' '.$this->user->lastname)?> for the period 
<?php $this->o(date('Y-m-d', strtotime($this->model->from)))?> to 
<?php $this->o(date('Y-m-d', strtotime($this->model->to)))?>.
</h4>
<p>
<label>Job Title: </label>
<?php $this->o($this->model->position)?>
</p>
<p>
<label>Reports To: </label>
<?php $this->o($this->model->reportsto) ?>
</p>
<p>

</p>
