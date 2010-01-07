<h2>
<?php $this->o($this->model->id ? 'Edit '.$this->model->title : 'New Timesheet');?>
</h2>

<?php if ($this->model->id): ?>
	<?php if ($this->model->locked): ?>
		<form method="post" action="<?php echo build_url('timesheet', 'unlock')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<p>
				Unlocking this timesheet will allow all contained records to be used
				in other timesheets.
			</p>
			<input type="submit" value="Unlock" style="padding-left: 16px; background: url('<?php echo resource('images/unlock.png')?>') no-repeat; " />
		</form>
	<?php else: ?>
		<form method="post" action="<?php echo build_url('timesheet', 'lock')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<p>
				Locking this timesheet prevents records covered by this timesheet
				appearing in other timesheets. 
			</p>
			<input type="submit" value="Lock" style="padding-left: 16px; background: url('<?php echo resource('images/lock.png')?>') no-repeat; " />
		</form>
	<?php endif; ?>
<?php endif;?>
<form method="post" action="<?php echo build_url('timesheet', 'save');?>">

<?php if (isset($this->project)): ?>
<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
<?php endif;?>

<?php if (isset($this->client)): ?>
<input type="hidden" value="<?php echo $this->client->id?>" name="clientid" />
<?php endif;?>

<?php if ($this->model->id): ?>
<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>

<div class="inner-column">
    <p>
    <label for="title">Timesheet Title:</label>
    <input class="input" type="text" name="title" 
        id="title" value="<?php echo $this->model->title?>" />
    </p>
    
    <?php $this->selectList('Task Types', 'tasktype', $this->categories, '', '', '', 4)?>
    
</div>
<div class="inner-column">
    <p>
    <label for="from">From:</label>
    <input <?php echo $this->model->locked ? 'disabled="disabled"' : ''?> readonly="readonly" type="text" class="input" name="from" id="from" value="<?php echo date('Y-m-d', strlen($this->model->from) ? strtotime($this->model->from) : time());?>" />
    <?php $this->calendar('from', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    <p>
    <label for="to">To:</label>
    <input <?php echo $this->model->locked ? 'disabled="disabled"' : ''?> readonly="readonly" type="text" class="input" name="to" id="to" value="<?php echo date('Y-m-d', strlen($this->model->to) ? strtotime($this->model->to) : time());?>" />
    <?php $this->calendar('to', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    
</div>
<p class="clear">
    <input <?php echo $this->model->locked ? 'disabled="disabled"' : ''?> type="submit" class="abutton" value="Save" accesskey="s" />
<?php if (isset($this->project)): ?>
    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#timesheet'))?>'" value="Cancel" />
<?php else: ?>
	<input type="button" class="abutton" onclick="location.href='<?php echo build_url('client', 'view', array('id'=>$this->client->id, '#timesheet'))?>'" value="Cancel" />
<?php endif; ?>

</p>
</form>