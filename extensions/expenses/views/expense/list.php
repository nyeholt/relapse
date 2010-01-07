<?php if ($this->u()->isPower()): ?>
<div id="parent-links">
	<a title="Add Expense" href="<?php echo build_url('expense', 'edit', isset($this->client) ? array('clientid'=>$this->client->id) : null)?>"><img src="<?php echo resource('images/add.png')?>"/></a>
    <a title="User List" href="<?php echo build_url('index', 'index', null, false, 'expenses');?>"><img src="<?php echo resource('images/client.png')?>"/></a>
</div>
<?php endif;?>


<h3>
Expense Reports
</h3>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="20%">From</th>
        <th width="20%">To</th>
        <th>Title</th>
        <th>Total</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->reports as $expenseReport): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><?php $this->o(date('Y-m-d', strtotime($expenseReport->from)));?></td>
        <td><?php $this->o(date('Y-m-d', strtotime($expenseReport->to)));?></td>
        <td><?php $this->o($expenseReport->title);?></td>
        <td>$<?php $this->o(sprintf('%.2f', $expenseReport->total));?></td>
        <td>
        <?php if ($expenseReport->locked): ?>
        	<a href="<?php echo build_url('expense', 'view', array('id'=>$expenseReport->id))?>"><img src="<?php echo resource('images/eye.png');?>" title="View Expenses"/></a>
        	<a href="<?php echo build_url('expense', 'view', array('pdf'=> 1, 'id'=>$expenseReport->id))?>"><img src="<?php echo resource('images/adobe.png');?>" title="Export to PDF"/></a>
        <?php endif; ?>
        <?php if (!$expenseReport->locked): ?>
        	<form class="inline" method="post" action="<?php echo build_url('expense', 'lock')?>">
        		<input type="hidden" name="id" value="<?php $this->o($expenseReport->id)?>"></input>
        		<?php if (isset($this->client)): ?>
        		<input type="hidden" name="client" value="<?php $this->o($this->client->id)?>"/>
				<?php elseif (isset($this->user)): ?>
				<input type="hidden" name="username" value="<?php $this->o($this->user->username)?>"/>
				<?php endif; ?>
				<input class="inline" type="image" src="<?php echo resource('images/lock.png')?>" />
        	</form>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('expense', 'deletereport', array('id'=>$expenseReport->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
        <?php endif; ?>
            <a href="<?php echo build_url('expense', 'editreport', array('id'=>$expenseReport->id, 'clientid'=>$expenseReport->clientid, 'username'=>$expenseReport->username))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p>
<?php if (isset($this->client)): ?>
<a href="<?php echo build_url('expense', 'editreport', array('clientid'=>$this->client->id))?>" class="abutton">Create Report</a>
<a href="<?php echo build_url('expense', 'view', array('clientid'=>$this->client->id))?>" class="abutton">View Expenses</a>
<?php elseif (isset($this->user)): ?>
<a href="<?php echo build_url('expense', 'editreport', array('username'=>$this->user->username))?>" class="abutton">Create Report</a>
<a href="<?php echo build_url('expense', 'view', array('username'=>$this->user->username))?>" class="abutton">View Expenses</a>
<?php endif; ?>
</p>

<h3>
Expenses
</h3>
<p><a class="abutton" href="<?php echo build_url('expense', 'edit', isset($this->client) ? array('clientid'=>$this->client->id) : null)?>">Add Expense</a></p>
<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Expense Date</th>
        <th width="35%">Description</th>
        <th>Amount</th>
        <th>User</th>
        <th>Client</th>
        <th>Project (ID)</th>
        <th width="15%"><?php if ($this->u()->hasRole(User::ROLE_POWER)):?><input type="checkbox" id="expense-selectall" /><?php endif; ?></th>
    </tr>
    </thead>
    <tbody>
    <?php if ($this->u()->hasRole(User::ROLE_POWER)):?>
    <tr>
    	<td colspan="5">
    	
    	</td>
    	
    	<td>
    	With selected...
    	</td>
    	<td>
    	<select id="select-action" class="short">
    	<option></option>
    	<option>Approve</option>
    	<option>Deny</option>
    	</select>
    	</td>
    </tr>
    <?php endif; ?>
    <?php $curMonth = ''; ?>
    <?php $index=0; foreach ($this->expenses as $expense): ?>
    
    <?php 
    $clientService = za()->getService('ClientService');
    $expenseClient = $clientService->getClient($expense->clientid);
    $expMonth = date('F Y', strtotime($expense->expensedate));
    if ($expMonth != $curMonth) {
        $curMonth = $expMonth;
        ?>
    <tr>    
    	<td>
    	<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
    	<form class="inline" style="float: right;" method="post" action="<?php echo build_url('expense', 'monthreport')?>">
    	<input type="hidden" name="month" value="<?php $this->o($expense->expensedate)?>" />
    	<?php if (isset($this->client)): ?>
		<input type="hidden" name="clientid" value="<?php $this->o($this->client->id)?>" />    
    	<?php elseif (isset($this->user)): ?>
    	<input type="hidden" name="username" value="<?php $this->o($this->user->username)?>" />    	
    	<?php endif;?>
    	<input class="inline" type="image" src="<?php echo resource('images/adobe.png');?>" title="Create Report"></input>
    	</form>
    	<?php endif; ?>
    	<?php $this->o($curMonth); ?>
    	
    	
    	</td>
    	<td colspan="6">
    	
    	</td>
    </tr>
        <?php 
    }?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><?php $this->o(date('Y-m-d', strtotime($expense->expensedate)));?></td>
        <td><?php $this->o($expense->description);?></td>
        <td><?php $this->o(sprintf("$%.2f", $expense->amount))?></td>
        <td>
        <?php $this->o($expense->username);?>
        </td>
        <td>
        <?php $this->o($expenseClient->title);?>
        </td>
        <td>
        <?php $this->o($expense->getProjectTitle());?> (<?php $this->o($expense->projectid);?>)
        </td>        
        <td>
        <?php if (za()->getUser()->hasRole(User::ROLE_POWER) && empty($expense->paiddate)): ?>
        	<input type="checkbox" value="<?php echo $expense->id?>" class="expense-selector" />
        <?php endif; ?>
        
        <?php if ($expense->status == Expense::APPROVED): ?>
        	<img title="approved" src="<?php echo resource('images/accept.png')?>" />
       	<?php endif;?>
       	
        <?php if ($expense->status == Expense::DENIED): ?>
        	<img title="denied" src="<?php echo resource('images/cross.png')?>" />
       	<?php endif;?>
        <a href="<?php echo build_url('expense', 'edit', array('id'=>$expense->id, 'clientid'=>$expense->clientid))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
        <?php if (!empty($expense->paiddate)): ?>
        	<img src="<?php echo resource('images/coins.png')?>" title="Paid <?php $this->o($this->u()->formatDate($expense->paiddate))?>" />
        
        <?php else: ?>
            
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('expense', 'delete', array('id'=>$expense->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
		<?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    
    </tbody>
</table>
<p><a class="abutton" href="<?php echo build_url('expense', 'edit', isset($this->client) ? array('clientid'=>$this->client->id) : null)?>">Add Expense</a></p>
<script type="text/javascript">
	$().ready(function() {
		var expenseIndex = $("#expenses-index");
		if (expenseIndex) {
			expenseIndex.html("Expenses (<?php echo count($this->expenses)?>)");
		}
		
		$('#expense-selectall').click(function() {
			$('.expense-selector').each (function() {
				this.checked = !this.checked;
			});
		});
		
		$('#select-action').change(function() {
			// Go through and build a string of all the selected items
			var action = $(this).val();
			if (action.length > 0) {
				var selected = "";
				var sep = "";
				$('.expense-selector').each (function() {
					if (this.checked) {
						selected += (sep + $(this).val());
						sep = ",";
					}
					
				});
				if (selected.length > 0) {
				<?php 
				$urlParams = array();
				if (isset($this->user)) {
				    $urlParams = array('user' => $this->user->username);
				} else {
				    $urlParams = array('clientid' => $this->client->id);
				} 
				?>
					var url = '<?php echo build_url('expense', 'domultiple', $urlParams)?>expenseaction/'+action+'/selected/'+selected;
					location.href=url;
					return true;
				}
			}
		});
	});
</script>

