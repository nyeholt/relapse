
<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
	<a href="<?php echo build_url('task', 'list', array('all' => 'true'))?>">Show all</a>
<?php endif; ?>

<h2>Tasks requiring assignment to correct job</h2>

<table class="item-table" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>Title</th>
			<th>Due</th>
        	<th>Assigned To</th>
        	<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php 
    	$taskRowView = new CompositeView('task/task-line-item.php'); 
    	
    	foreach ($this->unassignedTasks as $task) { 
            $taskRowView->task = $task;
            $taskRowView->project = $this->project;
            $taskRowView->showActions = true;
            echo $taskRowView->render('null');
    	} ?>
	</tbody>
</table>

<h2>Tasks</h2>
<table class="item-table" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>Title</th>
			<th>Due</th>
        	<th>Assigned To</th>
        	<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php 
    	$taskRowView = new CompositeView('task/task-line-item.php'); 
    	
    	foreach ($this->tasks as $task) { 
            $taskRowView->task = $task;
            $taskRowView->project = $this->project;
            $taskRowView->showActions = true;
            echo $taskRowView->render('null');
    	} ?>
	</tbody>
</table>
