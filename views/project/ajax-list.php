<ul>
<?php foreach ($this->projects as $project): ?>
    
<li>
	<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	<a class="action-icon" title="Edit" href="<?php echo build_url('project', 'edit', array('clientid' => $this->client->id, 'id'=> $project->id))?>">
	<img class="small-icon" src="<?php echo resource('images/pencil.png');?>" />
	</a>
	<a class="action-icon" title="Delete Project" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('project', 'delete', array('id' => $project->id))?>'; return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
	
	<a class="action-icon" title="View" href="<?php echo build_url('project', 'view', array('id' => $project->id));?>"><img class="small-icon" src="<?php echo resource('images/eye.png');?>" /></a>
	<?php endif; ?>
	
	<a href="<?php echo build_url('project', 'view', array('id' => $project->id));?>">
	<?php $this->o($project->title)?>
	</a>
</li>
<?php endforeach; ?>
</ul>

<script type="text/javascript">
	$().ready(function() {
		var projectIndex = $("#projects-index");
		if (projectIndex) {
			projectIndex.html("Projects (<?php echo count($this->projects)?>)");
		}
	});
</script>