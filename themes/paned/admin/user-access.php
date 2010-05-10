<div id="parent-links">
    <a href="<?php echo build_url('admin', 'index')?>">Config</a>
	<a href="<?php echo build_url('admin', 'userlist')?>">User List</a>
	<a href="<?php echo build_url('admin', 'grouplist')?>">Group List</a>
	<a href="<?php echo build_url('leave', 'list')?>">Leave</a>
</div>
<h2>User access for "<?php $this->o($this->user->username)?>"</h2>

<table class="item-table">
	<thead>
		<tr>
		<th>Module</th>
		<th>Action</th>
		<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->access as $access): ?>
		<form method="post" action="<?php echo build_url('admin', 'updateaccess')?>">
		<input type="hidden" name="id" value="<?php echo $access->id?>" />
		<input type="hidden" name="username" value="<?php $this->o($this->user->username)?>" />
		<tr>
			<td>
				<select name="accessmodule">
				<?php foreach ($this->modules as $module): ?>
				<option <?php echo $module == $access->module ? 'selected="selected"' : ''?>><?php $this->o($module)?></option>
				<?php endforeach; ?>
				</select>
			</td>
			<td>
				<select name="accessaction">
					<option <?php echo 'grant' == $access->action ? 'selected="selected"' : ''?>>grant</option>
					<option <?php echo 'deny' == $access->action ? 'selected="selected"' : ''?>>deny</option>
				</select>
			</td>
			<td>
				<input type="submit" name="doaction" value="Update" class="abutton" />
				<input type="submit" name="doaction" value="Delete" class="abutton" />
			</td>
		</tr>
		</form>
		<?php endforeach; ?>
		<tr>
		<td>Add new access</td>
		<td></td><td></td>
		</tr>
		<form method="post" action="<?php echo build_url('admin', 'updateaccess')?>">
		<input type="hidden" name="username" value="<?php $this->o($this->user->username)?>" />
		<tr>
			<td>
				<select name="accessmodule">
				<?php foreach ($this->modules as $module): ?>
				<option><?php $this->o($module)?></option>
				<?php endforeach; ?>
				</select>
			</td>
			<td>
				<select name="accessaction">
					<option>grant</option>
					<option>deny</option>
				</select>
			</td>
			<td>
				<input type="submit" name="doaction" value="Add" class="abutton" />
			</td>
		</tr>
		</form>
	</tbody>
</table>