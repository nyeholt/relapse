<div id="parent-links">
    <a href="<?php echo build_url('admin', 'index')?>">Config</a>
	<a href="<?php echo build_url('admin', 'userlist')?>">User List</a>
	<a href="<?php echo build_url('admin', 'grouplist')?>">Group List</a>
	<a href="<?php echo build_url('leave', 'list')?>">Leave</a>
</div>
<h2>Group Members</h2>
<form action="<?php echo build_url('admin', 'savegroupusers')?>" method="post">
<input type="hidden" name="id" value="<?php echo $this->group->id?>" />
<p>
<label for="group-users">Users</label>
<select name="groupusers[]" multiple="multiple" size="10" id="group-users">
    <?php 
    foreach ($this->users as $user): ?>
    <option value="<?php echo $user->id?>" <?php echo isset($this->groupusers[$user->id]) ? 'selected="selected"' : '';?>><?php echo $this->o($user->getUsername())?></option>
    <?php endforeach; ?>
</select>
</p>
<p>
<input type="submit" class="abutton" value="Save" />
</p>

</form>
