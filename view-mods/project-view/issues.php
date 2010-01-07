<div id="issues">
	<h4>Requests</h4>

    <?php $view->dispatch('issue', 'projectlist', array('projectid'=>$view->project->id)); ?>

    <p>
	<a class="abutton" href="<?php echo build_url('issue', 'edit', array('projectid'=>$view->project->id))?>">Create Request</a>
    </p>
</div>