<?php if (!$this->project->ismilestone): ?>
<div class="std">
	<h3>Requests</h3>
	<?php $this->dispatch('issue', 'projectlist', array('projectid'=>$this->project->id)); ?>
	<p>
	<a class="abutton" href="<?php echo build_url('issue', 'edit', array('projectid'=>$this->project->id))?>">Create Request</a>
	</p>
</div>
<?php endif ; ?>