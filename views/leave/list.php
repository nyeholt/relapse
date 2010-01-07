<h2>Leave</h2>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
    	<th>Who</th>
    	<th>Type</th>
        <th width="35%">Reason</th>
        <th>Requested</th>
        <th>From</th>
        <th>To</th>
        <th>Status</th>
        <th>Days</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->leaveApplications as $app): ?>
    <tr class="<?php echo $index % 2 == 0 ? 'even' : 'odd'?>">
    	<td><?php $this->o($app->username)?></td>
    	<td><?php $this->o($app->leavetype)?></td>
		<td><?php $this->o($app->reason)?></td>
		<td align="center"><?php $this->o($app->numdays)?></td>
		<td><?php $this->o(date('Y-m-d', strtotime($app->from))) ?></td>
		<td><?php $this->o(date('Y-m-d', strtotime($app->to))) ?></td>
		<td><?php $this->o($app->status) ?></td>
		<td align="center"><?php if ($app->status == LeaveApplication::LEAVE_APPROVED) echo $app->days; ?></td>
		<td>
		<a href="#" onclick="if (confirm('Are you sure?')) $('#application-<?php echo $index?>').show(); $('#leave-amount-<?php echo $index?>').load('<?php echo build_url('leave', 'calc', array('username'=>$app->username, 'type'=>$app->leavetype))?>'); return false; "><img src="<?php echo resource('images/accept.png')?>" /></a>
		<a href="#" onclick="if (!confirm('Are you sure you want to deny this leave application?')) return false; location.href='<?php echo build_url('leave', 'changestatus', array('status'=>'deny', 'id'=>$app->id))?>'; return false;"><img src="<?php echo resource('images/cross.png')?>" /></a>
		</td>
	</tr>
	<tr id="application-<?php echo $index?>" style="display: none;" class="<?php echo $index % 2 == 0 ? 'even' : 'odd'?>">
		<td colspan="9">
			<form method="post" action="<?php echo build_url('leave', 'changestatus', array('status'=>'approve', 'id'=>$app->id))?>">
				<p>How many days should be deducted from this person's leave? 
				Remember to account for weekends and public holidays</p>
				
				<input type="text" value="<?php echo $app->numdays?>" name="days" /> <input type="submit" value="Approve" />
				<span id="leave-amount-<?php echo $index?>">Please wait...</span>
			</form>
			
		</td>
	</tr>
	<?php $index++; endforeach; ?>
	</tbody>
</table>

<?php $this->pager($this->totalItems, $this->listSize, $this->pagerName); ?>