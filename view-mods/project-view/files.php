<div id="files">
    <div>
    <?php $view->dispatch('project', 'filelist', array('projectid'=>$view->project->id), null, array('folder')); ?>
    </div>
</div>