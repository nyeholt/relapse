<div id="codereviews">
    <?php $view->dispatch('codereview', 'listproject', array('projectid'=>$view->project->id)); ?>

    <p>
	<a class="abutton" href="<?php echo build_url('codereview', 'edit', array('projectid'=>$view->project->id))?>">Add Code Review</a>
    </p>
</div>