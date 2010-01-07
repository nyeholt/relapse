<div id="invoices">
    
    
    <div id="project-info-<?php echo $view->project->id?>-invoice">
    <?php $view->dispatch('invoice', 'projectlist', array('projectid'=>$view->project->id)); ?>
    </div>
    
    <p>
    <a class="abutton" href="<?php echo build_url('invoice', 'edit', array('projectid'=>$view->project->id))?>">Add Invoice</a>
    </p>
</div>