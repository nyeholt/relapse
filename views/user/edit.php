<h2>Editing <?php $this->o($this->model->username) ?> </h2>

<script type="text/javascript">
<!--
$().ready(function() {
	$("#edit-user-container").tabs({ fxFade: true, fxSpeed: 'fast' });
});
//-->
</script>

<div id="edit-user-container">
	<ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->hasRole(User::ROLE_USER)): ?>
        <li><a href="#leave"><span>Leave</span></a></li>
        <li><a href="#expenses"><span>Expenses</span></a></li>
        <li><a href="#reviews"><span>Reviews</span></a></li>
        <?php endif; ?>
    </ul>

	<div id="details">
	
<form method="post" action="<?php echo build_url('user', 'save', array('id' => $this->model->id));?>">

<?php if (!$this->model->contactid):  // if the user has a contact id, don't allow changing details here. ?>
<p>
    <label for="email">Email Address</label>
    <input id="email" type="text" value="<?php $this->o($this->model->email)?>" name="email" size="20" maxlength="40" />
</p>

<p>
    <label for="firstname">First Name</label>
    <input id="firstname" type="text" value="<?php $this->o($this->model->firstname)?>" name="firstname" size="20" maxlength="40" />
</p>
<p>
    <label for="lastname">Last Name</label>
    <input id="lastname" type="text" value="<?php $this->o($this->model->lastname)?>" name="lastname" size="20" maxlength="40" />
</p>

<?php elseif ($this->model->hasRole(User::ROLE_USER)): ?>
<p>
	This user's details can be updated via their <a href="<?php echo build_url('contact', 'edit', array('id'=>$this->model->contactid))?>">Contact Details Page</a>.
</p>
<?php endif; ?>


<?php if ($this->model->hasRole(User::ROLE_USER)): ?>
	<?php if (za()->getUser()->isPower()): ?>
	<p>
	    <label for="startdate">Started:</label>
	    <input readonly="readonly" type="text" class="input" name="startdate" id="startdate" value="<?php echo $this->model->startdate ? date('Y-m-d', strtotime($this->model->startdate)) : date('Y-m-d', time())?>" />
	    <?php $this->calendar('startdate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
	</p>
	
	<!-- control for selecting the contact this user is associated with -->
	<?php $this->selectList('Contact Record', 'contactid', $this->contacts, $this->model->contactid, 'id', array('firstname', 'lastname'), false, true); ?>
	        	
	<?php endif; ?>
<?php endif; ?>

<p><label for="password">Password</label><input id="password" type="password" name="password" size="20" maxlength="40" /></p>
<p><label for="confirm" style="clear: both;">Confirm Password</label><input id="confirm" type="password" name="confirm" size="20" maxlength="40" /></p>
<?php $this->selectList('Theme', 'theme', $this->themes) ?>
<p>
    <input type="submit" class="abutton" name="submit" value="Update" accesskey="s" />
</p>
</form>
	</div>

<?php if ($this->model->hasRole(User::ROLE_USER)): ?>
	<div id="leave">
		<table class="item-table" cellpadding="0" cellspacing="0">
		    <thead>
		    <tr>
		        <th width="35%">Reason</th>
		        <th>From</th>
		        <th>To</th>
		        <th>Status</th>
		        <th>Days</th>
		        <th>&nbsp;</th>
		    </tr>
		    </thead>
		    <tbody>
		    <?php $index=0; $leaveTotal = 0; $leaveTotals = array();
		    foreach ($this->leaveApplications as $app): ?>
		    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
				<td><?php $this->o($app->reason)?></td>
				<td><?php $this->o(date('Y-m-d', strtotime($app->from))) ?></td>
				<td><?php $this->o(date('Y-m-d', strtotime($app->to))) ?></td>
				<td><?php $this->o($app->status) ?></td>
				<td><?php 
				if ($app->status == LeaveApplication::LEAVE_APPROVED) { 
					$current = ifset($leaveTotals, $app->leavetype, 0);
					$current += $app->days;
					$leaveTotals[$app->leavetype] = $current;
					echo $app->days;
					
				} 
				?>
				</td>
				<td>
				<?php if ($app->status != 'Approved'): ?>
					<a href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('leave', 'delete', array('id'=>$app->id))?>'; return false;"><img src="<?php echo resource('images/delete.png')?>" /></a>
				<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p>
		<input class="abutton" onclick="location.href='<?php echo build_url('leave', 'apply', array('userid' => $this->model->id))?>';" value="Apply for Leave"/>
			<?php if (za()->getUser()->isPower()): ?>
				<a class="abutton" href="<?php echo build_url('leave', 'edit', array('id'=>$this->leave->id))?>">Change additional leave</a>
			<?php endif; ?>
		</p>
		
		<?php foreach ($leaveTotals as $type => $total): ?>
			<?php if ($type == 'Annual'): ?>
			<p>
				About <?php $this->o(sprintf("%d", floor($this->leave->days + $this->accruedLeave - $leaveTotals['Annual']))) ?> days of annual leave available.
			</p>
			<?php else: ?>
			<p>
				<?php $this->o(ifset($leaveTotals, $type, 0)) ?> days of <?php $this->o($type)?> leave taken
			</p>
				
			<?php endif; ?>
		<?php endforeach; ?>
		
		
		<p>
			
		</p>
		

	</div>
	
	<div id="expenses">
		<?php $this->dispatch('expense', 'listforuser', array('username'=>$this->model->username))?>
	</div>

	<div id="reviews">
		<?php $this->dispatch('performancereview', 'list', array('username'=>$this->model->username))?>
	</div>

	<?php endif; ?>
<!--/end container div -->
</div>