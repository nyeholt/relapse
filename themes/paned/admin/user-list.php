<div class="std">
<div id="parent-links">
    <a href="<?php echo build_url('admin', 'index')?>">Config</a>
	<a href="<?php echo build_url('admin', 'userlist')?>">User List</a>
	<a href="<?php echo build_url('admin', 'grouplist')?>">Group List</a>
	<a href="<?php echo build_url('leave', 'list')?>">Leave</a>
</div>

<h2>Users</h2>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Username</th>
        <th width="50%">&nbsp;</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->users as $user): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><p><?php $this->o($this->ellipsis($user->getUsername(), 40));?></p></td>
        <td align="right">
        <form method="post" action="<?php echo build_url('admin', 'changerole')?>" class="inline">
        	<input type="hidden" name="id" value="<?php echo $user->id?>" />
        	<select name="role">
        		<?php foreach ($this->roles as $role): ?>
        			<option value="<?php $this->o($role) ?>" <?php echo $role == $user->getRole() ? 'selected="selected"' : ''?>><?php $this->o($role)?></option>
        		<?php endforeach; ?>
        	</select>
        	<input type="submit" value="Change Role" />
        </form>
		<input type="button" value="Reset Password" onclick="resetPass('<?php $this->o($user->email)?>')"/>
        </td>
        <td>
        	<a href="<?php echo build_url('user', 'edit', array('id' => $user->getId()));?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
        	<a href="<?php echo build_url('admin', 'useraccess', array('username' => $user->getUsername()));?>" title="User Access"><img src="<?php echo resource('images/bullet_go.png')?>" /></a>
			
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</div>

<script type="text/javascript">
	function resetPass(email) {
		if (confirm("Really reset "+email+"?")) {
			jQuery.post('<?php echo build_url('user', 'password')?>', {email: email});
		}
	}
</script>