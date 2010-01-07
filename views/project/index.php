<?php if (!$this->hideHeader): ?>
<h2>Projects</h2>
<?php endif;?>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
    	<th>ID</th>
        <th>Title</th>
        <?php if (!$this->hideHeader): ?>
        <th>Client</th>
        <?php endif; ?>
        <th>Due</th>
        <th>Completed</th>
        <th>Free Support End</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->projects as $project): ?>
    <?php 
    $dueClass = ''; 
    $completeClass = '';
    if (strtotime($project->due) < time() && !$project->isComplete()) {
    	$dueClass = 'overdue'; 
    }
    if ($project->isComplete()) {
    	$completeClass = 'project-complete';
    }
    ?> 
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?> <?php echo $completeClass?>">
    	<td><?php echo $project->id?></td>
        <td><a href="<?php echo build_url('project', 'view', array('id'=>$project->id))?>"><?php $this->o($project->title);?></a></td>
        <?php if (!$this->hideHeader): ?>
        <td><a href="<?php echo build_url('client', 'view', array('id'=>$project->clientid, '#projects'))?>"><?php $this->o($project->clienttitle);?></a></td>
        <?php endif; ?>
        <td><span class="<?php echo $dueClass?>"><?php $this->o(date('F jS Y', strtotime($project->due)))?></span></td>
        <td><?php if ($project->isComplete()): ?>
        	<?php $this->o(date('F jS Y', strtotime($project->completed)))?>
        	<?php else: ?>&nbsp;<?php endif; ?>
        </td>
        <td><?php if ($project->startfgp): ?>
            <?php $this->o(date('l dS M, Y', $project->getFreeSupportEndDate())); ?>
            <?php else: ?>&nbsp;<?php endif; ?>
        </td>
        <td>
        <?php if ($this->u()->hasRole(User::ROLE_USER) && !$project->isComplete()): ?>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('project', 'delete', array('id'=>$project->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
            <a href="<?php echo build_url('project', 'edit', array('id'=>$project->id, 'clientid'=>$project->clientid))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
        <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>


<?php $this->AtoZPager($this->letters, $this->pagerName, true); ?>
