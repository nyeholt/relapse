<div class="project-overview">
  	<h4>Overdue Tasks</h4>
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

  	foreach ($this->project->getOverview()->offsetGet('overdueTasks') as $task) { 

          $taskRowView->task = $task;
          $taskRowView->project = $this->project;
          echo $taskRowView->render('null');
  	} ?>
  	</tbody>
  	</table>
  	
  	<h4>Due in the next week</h4>
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

  	foreach ($this->project->getOverview()->offsetGet('dueSoon') as $task) { 

          $taskRowView->task = $task;
          $taskRowView->project = $this->project;
          echo $taskRowView->render('null');
  	} ?>
  	</tbody>
  	</table>
  	
  	<h4>Open Issues</h4>
<table class="item-table" cellpadding="0" cellspacing="0">
   <thead>
   <tr>
   	<th width="30%">Title</th>
   	<th width="40%">Created</th>
   	<th width="15%">Severity</th>
   	<th width="15%"></th>
   	</tr>
   </thead>
   <?php foreach ($this->project->getOverview()->offsetGet('openIssues') as $issue): ?>
   	<tr>
   		<td><a href="<?php echo build_url('issue', 'edit', array('id'=>$issue->id))?>"><?php $this->o($issue->title); ?></a></td>
   		<td><?php $this->o($issue->created); ?></td>
   		<td><?php $this->o($issue->severity); ?></td>
   		<td><?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
           <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('issue', 'delete', array('id'=>$issue->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
           <?php endif; ?>
        </td>
   	</tr>	
   <?php endforeach; ?>
</table>
  	
      <p>
      
      <?php $this->addNote($this->project->title, $this->project->id, 'project');?>
      <?php $this->viewNotes($this->project->id, 'project');?>
      </p>
      
  </div>