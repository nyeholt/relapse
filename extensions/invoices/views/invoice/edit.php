<h2>
<?php $this->o($this->model->id ? 'Edit '.$this->model->title : 'New Invoice');?>
</h2>
<form method="post" action="<?php echo build_url('invoice', 'save');?>">
<?php if (isset($this->project)): ?>
<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
<?php endif;?>
<?php if ($this->model->id): ?>
<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
<div class="inner-column">
	<?php $this->textInput('Invoice Title', 'title')?>
	<?php $this->textInput('Amount Paid', 'amountpaid')?>
    
</div>
<div class="inner-column">
	<?php $this->selectList('Timesheet', 'timesheetid', $this->timesheets, '', 'id', 'title')?>

</div>
<p class="clear">
    <input type="submit" class="abutton" value="Save" accesskey="s" />
    <input type="button" class="abutton" onclick="history.go(-1);" value="Close" />
</p>
</form>