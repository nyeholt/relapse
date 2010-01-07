<div id="expenses">
    <div id="client-info-<?php echo $view->client->id?>-expenses">
    <?php $view->dispatch('expense', 'list', array('clientid'=>$view->client->id)); ?>
    </div>
</div>