
<form action="<?php echo build_url('project', 'updategroup')?>" method="post">
<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
<input type="hidden" name="groupid" value="<?php echo $this->group->id?>" />
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
