<script type="text/javascript">
    $().ready(function() {
        $("select#clientid").change(function(){
           $.getJSON('<?php echo build_url('project', 'clientprojects')?>', {clientid: $(this).val(), ajax: 'true'}, function(data) {
               var options = '';
                for (var i = 0; i < data.length; i++) {
                    options += '<option value="' + data[i].id + '">' + data[i].title + '</option>';
                }
                $("select#projectid").html(options);
                
           }); 
        });
        
        if ($("select#username").val() != null && $("select#username").val().length > 0) {
        	$("#client-project-lists").hide();
        }
        
        $("select#username").change(function() {
        	// if a variable is selected, we want to hide the 
        	// options for client and project. 
        	if ($(this).val().length > 0) {
        		$("#client-project-lists").hide();
        	} else {
        		$("#client-project-lists").show();
        	}	
        });
        
    });
</script>

<h2>
<?php $this->o($this->model->id ? 'Edit '.$this->model->title : 'New Expense Report');?>
</h2>

<?php if ($this->model->id): ?>
	<?php if ($this->model->locked && empty($this->model->paiddate)): ?>
		<form method="post" action="<?php echo build_url('expense', 'unlock')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<p>
				Unlocking this expense report will allow all contained records to be used
				in other reports.
			</p>
			<input type="submit" value="Unlock" style="padding-left: 16px; background: url('<?php echo resource('images/unlock.png')?>') no-repeat; " />
		</form>
	<?php elseif (!$this->model->locked): ?>
		<form method="post" action="<?php echo build_url('expense', 'lock')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<?php if ($this->client->id): ?>
			<input type="hidden" value="<?php $this->o($this->client->id)?>" name="clientid" />
			<?php endif;?>
			
			<?php if ($this->user->id > 0):  ?>
			<input type="hidden" value="<?php $this->o($this->user->username)?>" name="username" />
			<?php endif;?>
			<p>
				Locking this expense report prevents records covered by this report
				appearing in other reports. 
			</p>
			<input type="submit" value="Lock" style="padding-left: 16px; background: url('<?php echo resource('images/lock.png')?>') no-repeat; " />
		</form>
	<?php endif; ?>
<?php endif;?>
<form method="post" action="<?php echo build_url('expense', 'savereport');?>">

<?php if (isset($this->project)): ?>
<input type="hidden" value="<?php $this->o($this->project->id)?>" name="projectid" />
<?php endif;?>

<?php if ($this->client->id): ?>
<input type="hidden" value="<?php $this->o($this->client->id)?>" name="clientid" />
<?php endif;?>

<?php if ($this->user->id > 0):  ?>
<input type="hidden" value="<?php $this->o($this->user->username)?>" name="username" />
<?php endif;?>

<?php if ($this->model->id): ?>
<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>

<div class="inner-column">
    <p>
    <label for="title">Report Title:</label>
    <input class="input" type="text" name="title" 
        id="title" value="<?php echo $this->model->title?>" />
    </p>
    
    <?php $this->selectList('For User', 'username', $this->users, $this->user->username, 'username', 'username', false, true, $this->model->locked ? ' disabled="disabled"' : '')?>

	<div id="client-project-lists">
	    <p>
	    <label for="clientid">Client:</label>
	    <select name="clientid" id="clientid" <?php echo $this->model->locked ? 'disabled="disabled"' : ''?>>
	        <?php 
	        $sel = $this->model->clientid ? $this->model->clientid : $this->client->id;
	        foreach ($this->clients as $client): ?>
	            <option value="<?php echo $client->id?>" <?php echo $sel == $client->id ? 'selected="selected"' : '';?>><?php $this->o($client->title);?></option>
	        <?php endforeach; ?>
	    </select>
	    </p>
	    
	    <p>
	    <label for="projectid">Project:</label>
	    <select name="projectid" id="projectid" <?php echo $this->model->locked ? 'disabled="disabled"' : ''?>>
	    	<option value="0"></option>
	        <?php 
	        $sel = $this->model->projectid ? $this->model->projectid : '';
	        foreach ($this->projects as $project): ?>
	            <option value="<?php echo $project->id?>" <?php echo $sel == $project->id ? 'selected="selected"' : '';?>><?php $this->o($project->title);?></option>
	        <?php endforeach; ?>
	    </select>
	    </p>
    </div>
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
    
    <?php if ($this->model->locked): ?>
    <p>
    <label for="paiddate">Expenses paid on:</label>
    <input readonly="readonly" type="text" class="input" name="paiddate" id="paiddate" value="<?php echo strlen($this->model->paiddate) ? date('Y-m-d', strtotime($this->model->paiddate)) : '';?>" />
    <?php $this->calendar('paiddate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    <?php endif; ?>
    
</div>
<p class="clear">
    <input  type="submit" class="abutton" value="Save" accesskey="s" />
	<?php if ($this->client->id): ?> 
	<input type="button" class="abutton" onclick="location.href='<?php echo build_url('client', 'view', array('id'=>$this->client->id, '#expenses'))?>'" value="Done" />
	<?php else: ?>
	<input type="button" class="abutton" onclick="location.href='<?php echo build_url('expense', 'listforuser', array('username'=>$this->user->username))?>'" value="Done" />
	<?php endif; ?> 
</p>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Expense Date</th>
        <th width="35%">Description</th>
        <th>Amount</th>
        <th>User</th>
        <th width="15%"></th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->expenses as $expense): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><?php $this->o(date('Y-m-d', strtotime($expense->expensedate)));?></td>
        <td><?php $this->o($expense->description);?></td>
        <td><?php $this->o(sprintf("$%.2f", $expense->amount))?></td>
        <td>
        <?php $this->o($expense->username);?>
        </td>
        <td>
        
        <?php if ($expense->status == Expense::APPROVED): ?>
        	<img title="approved" src="<?php echo resource('images/accept.png')?>" />
       	<?php endif;?>
       	
        <?php if ($expense->status == Expense::DENIED): ?>
        	<img title="denied" src="<?php echo resource('images/cross.png')?>" />
       	<?php endif;?>
        
        <?php if (!empty($expense->paiddate)): ?>
        	<img src="<?php echo resource('images/coins.png')?>" title="Paid <?php $this->o($this->u()->formatDate($expense->paiddate))?>" />
		<?php endif; ?>
        </td>
    </tr>
    <?php endforeach;?> 
    </tbody>
</table>

</form>