<div id="parent-links">
    <a href="<?php echo build_url('admin', 'index')?>">Config</a>
	<a href="<?php echo build_url('admin', 'userlist')?>">User List</a>
	<a href="<?php echo build_url('admin', 'grouplist')?>">Group List</a>
	<a href="<?php echo build_url('leave', 'list')?>">Leave</a>
</div>
<h2>Groups</h2>
<form action="<?php echo build_url('admin', 'creategroup')?>" method="post">
<?php if ($this->model->id):?>
<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
<?php endif; ?>
<p>
<label for="title">Title</label>
<input  type="text" name="title" id="title" value="<?php echo $this->model->title?>" />
<select name="parent">
    <option value=''></option>
    <?php 
    $sel = $this->model->parentpath ? $this->model->parentpath : '';
    foreach ($this->groups as $group): ?>
    <option value="<?php echo $group->id?>" <?php echo $sel == $group->getPath() ? 'selected="selected"' : '';?>><?php echo $this->o($group->title)?></option>
    <?php endforeach; ?>
</select>

<input type="submit" class="abutton" value="<?php echo $this->model->id ? 'Update' : 'Create'?>" />
</p>


</form>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Title</th>
        <th width="10%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->groups as $group): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><?php echo $group->parentpath.$group->id; $this->o(' : '.$group->title);?></td>
        <td>
            <a href="<?php echo build_url('admin', 'viewgroup', array('id'=>$group->id))?>"><img src="<?php echo resource('images/eye.png')?>" /></a>
            <a href="<?php echo build_url('admin', 'grouplist', array('id'=>$group->id))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('admin', 'deletegroup', array('id'=>$group->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>