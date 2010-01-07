<h2>Apply for leave</h2>
<form action="<?php echo build_url('leave', 'saveapplication')?>" method="post">
<input type="hidden" name="id" value="<?php echo $this->model->id ?>" />
<input type="hidden" name="userid" value="<?php echo $this->user->id?>" />
<p>
    <label for="from">From:</label>
    <input readonly="readonly" type="text" class="input" name="from" id="from" value="<?php echo $this->model->from ? date('Y-m-d', strtotime($this->model->from)) : date('Y-m-d', time())?>" />
    <?php $this->calendar('from', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
</p>
<p>
    <label for="to">To:</label>
    <input readonly="readonly" type="text" class="input" name="to" id="to" value="<?php echo $this->model->to ? date('Y-m-d', strtotime($this->model->to)) : date('Y-m-d', time() + 86400)?>" />
    <?php $this->calendar('to', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
</p>

<?php $this->textInput('How many days?', 'numdays') ?>

<?php $this->selectList('Leave Type', 'leavetype', $this->leaveTypes); ?>
<p>
	<label for="reason">Reason</label>
	<textarea id="reason" name="reason"><?php $this->o($this->model->reason)?></textarea>
</p>

<input type="submit" value="Apply" />
</form>