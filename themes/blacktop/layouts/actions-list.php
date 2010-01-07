<script type="text/javascript">

function completeTask(id)
{
	var endpoint = '<?php echo  build_url('task', 'complete');?>id/'+id;
	$.get(endpoint, function (data) {
		// remove the item
		$('#task-list-id-'+id).remove();
	});
}
</script>

<div id="action-list" class="box">
    <div class="action-list-item">
    	<h2>Your Tasks ...</h2>
        <ul>
        <?php foreach ($this->tasks as $task): ?>
        <li id="task-list-id-<?php echo $task->id?>">
            <a title="Start Timer" href="#" onclick="popup('<?php echo build_url('timesheet', 'record', array('id' => $task->id))?>', 'timer', '500', '300'); return false;"><img class="small-icon" src="<?php echo resource('images/clock_play.png')?>" />
            </a>
	        <a title="Mark as complete" href="#" onclick="if (confirm('Really?')) { completeTask(<?php echo $task->id?>); } return false;"><img class="small-icon" src="<?php echo resource('images/accept.png')?>" />
            </a>
            <a title="Go To Task" href="<?php echo build_url('task', 'edit', array('id'=>$task->id));?>">
            <?php $this->o($task->title); ?>
            </a>
            <p class="task-owning-project">
            [<a title="Go to Project" href="<?php echo build_url('project', 'view', array('id'=>$task->projectid));?>"><?php $this->o($task->getProjectTitle()) ?></a>]
            </p>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>