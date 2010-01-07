<script type="text/javascript">
$().ready(function() {
	var status = $.cookie('list_status'); 
	if (status == 'hide') {
		$('#action-list-wrapper').slideUp();
		$('#action-list-toggle').toggle(
			function (){
				$('#action-list-wrapper').slideDown();
				$.cookie('list_status', 'show', {path: '/'});
			},
			function(){ 
				$('#action-list-wrapper').slideUp();
				$.cookie('list_status', 'hide', {path: '/'});
			}
		);
	} else {
		$('#action-list-toggle').toggle(
		function(){ 
			$('#action-list-wrapper').slideUp();
			$.cookie('list_status', 'hide', {path: '/'});
		},
		function (){
			$('#action-list-wrapper').slideDown();
			$.cookie('list_status', 'show', {path: '/'});
		}
		);
	}
});

function completeTask(id)
{
	var endpoint = '<?php echo  build_url('task', 'complete');?>id/'+id;
	
	$.get(endpoint, function (data) {
		// remove the item
		$('#task-list-id-'+id).remove();
	});
}
</script>

<img id="action-list-toggle" src="<?php echo resource('images/page_wide.png');?>" />

<div id="action-list-wrapper">
<div id="action-list">
    <div class="action-list-item">
    	<a title="Add new task" class="action-icon" href="<?php echo build_url('task', 'edit');?>"><img src="<?php echo resource('images/add.png'); ?>" /></a>
        <h4>Your Tasks</h4>
        <ul>
        <?php foreach ($this->tasks as $task): ?>
        <li id="task-list-id-<?php echo $task->id?>">
            <a title="Start Timer" class="action-icon" href="#" onclick="popup('<?php echo build_url('timesheet', 'record', array('id' => $task->id))?>', 'timer', '500', '300'); return false;"><img class="small-icon" src="<?php echo resource('images/clock_play.png')?>" />
            </a>
	        <a title="Mark as complete" class="action-icon" href="#" onclick="if (confirm('Really?')) { completeTask(<?php echo $task->id?>); } return false;"><img class="small-icon" src="<?php echo resource('images/accept.png')?>" />
            </a>
            <p>
            <a title="Go To Task" href="<?php echo build_url('task', 'edit', array('id'=>$task->id));?>">
            <?php $this->o($task->title); ?>
            </a><br/>
            </p>
            <p class="task-owning-project">
            [<a title="Go to Project" href="<?php echo build_url('project', 'view', array('id'=>$task->projectid));?>"><?php $this->o($task->getProjectTitle()) ?></a>]
            </p>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
</div>