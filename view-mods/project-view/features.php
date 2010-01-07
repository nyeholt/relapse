<div id="features">
    <form action="<?php echo build_url('feature', 'createtasks')?>" method="post">
    <input type="hidden" name="projectid" value="<?php echo $view->project->id?>" />
    <div id="project-info-<?php echo $view->project->id?>-feature">
        <?php $view->dispatch('feature', 'projectlist', array('projectid'=>$view->project->id)); ?>
    </div>
    <p>
    <input style="float: right;" class="abutton" type="submit" value="Create Tasks" />
    <a class="abutton" title="Add Feature" href="<?php echo build_url('feature', 'edit', array('projectid'=>$view->project->id))?>">Add Feature</a>
    <a class="abutton" title="Recalculate Project Estimate" href="<?php echo build_url('feature', 'recalculate', array('projectid'=>$view->project->id))?>">Calculate Cost</a>

    </p>
    </form>
</div>