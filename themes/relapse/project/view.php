
<?php $label = $this->project->ismilestone ? 'Milestone' : 'Project';?>

<?php if ($this->project->id): ?>

<?php
// info for whether to display the 'add to favourites' stuff 
$deleteStyle = isset($this->existingWatch) && $this->existingWatch ? 'inline' : 'none'; 
$addStyle = $deleteStyle == 'inline' ? 'none' : 'inline';
?>
	        
<div id="parent-links">
	<?php if ($this->project->parentid): ?>
	<a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->parentid));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
	<?php endif; ?>
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->project->clientid));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
    
    <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
    <a title="Expand" href="#" onclick="return displayProjectTree(this, <?php echo $this->project->clientid?>, 'Client', '<?php echo build_url('tree', 'view')?>');"><img src="<?php echo resource('images/tree.png')?>"/></a>
    
    <a title="Unsubscribe" style="display: <?php echo $deleteStyle?>;" id="delete-project-watch" href="#" onclick="$.post('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->project->id?>', type:'<?php echo get_class($this->project)?>'}, function() {$('#delete-project-watch').hide();$('#add-project-watch').show(); }); return false; "><img src="<?php echo resource('images/thumb_down.png')?>"/></a>
	<a title="Subscribe" style="display: <?php echo $addStyle?>;" id="add-project-watch" href="#" onclick="$.post('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->project->id?>', type:'<?php echo get_class($this->project)?>'}, function() {$('#add-project-watch').hide();$('#delete-project-watch').show(); }); return false; "><img src="<?php echo resource('images/thumb_up.png')?>"/> </a>

    <?php endif; ?>
</div>
<?php $this->hierarchy($this->project->getHierarchy()); ?>
<!-- end if has project id -->
<?php endif; ?>

<div class="project-tree" id="project-<?php echo $this->project->clientid?>-tree" style="display: none;">
</div>

<h2><?php $this->o($this->project->title.' (#'.$this->project->id.')')?></h2>

<div id="project-container">
    <ul class="tab-options">
    	<li><a href="#overview"><span>Overview</span></a></li>
    	<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
    	<li><a href="#group-users"><span>Users</span></a></li>
    	<?php endif; ?>
        <?php if ($this->project->ismilestone): ?> 
        <li><a href="#tasks"><span id="tasks-index">Tasks</span></a></li>
        <?php else: ?>
        <li><a href="#tasks"><span id="tasks-index">Tasks</span></a></li>
        <li><a href="#status"><span>Status</span></a></li>
    	<li><a href="#sub-projects"><span id="sub-projects-index">Sub Projects</span></a></li>
        <li><a href="#timesheet"><span>Timesheets</span></a></li>
        <li><a href="#features"><span id="features-index">Features</span></a></li>
        <li><a href="#files"><span>Files</span></a></li>
        <?php endif; ?>
    </ul>

    <div id="overview">
    
    <div class="bordered">
    	<h3>Details</h3>
    	<div class="wide-form">
    	<p>
    	<label>Description</label>
    		<?php $this->wikiCode($this->project->description) ?>
    	</p>
    	</div>
    	<div class="inner-column">
	    	<p>
	    	<label>Start Date</label><?php $this->o(date('l dS M, Y', strtotime($this->project->started))); ?>
	    	</p>
	    	<p>
		    <label for="actualstarted">Actual Start:</label>
		    <?php if ($this->project->actualstart): ?>
		    <?php $this->o(date('F jS Y', strtotime($this->project->actualstart))); ?>
		    <?php else: ?>
		    &nbsp;
		    <?php endif; ?>
		    </p>
	    	<p>
	    	<label>Due Date</label>
	    	<?php $this->o($this->project->due ? date('l dS M, Y', strtotime($this->project->due)) : '_'); ?>
	    	</p>
	    	<?php if ($this->project->url):?>
	    	<p>
	    	<label>Website</label>
	    	<a href="<?php $this->o($this->project->url); ?>"><?php $this->o($this->project->url); ?></a>	    	
	    	</p>
	 		<?php endif; ?>
	    	
    	</div>
    	<div class="inner-column">
			<p>
	    	<label>Estimated Days</label><?php $this->o($this->project->estimated > 0 ? $this->project->estimated : 0); ?>
	    	</p>
	    	<p>
	    	<label>... by Tasks</label><?php $this->o($this->project->taskestimate > 0 ? $this->project->taskestimate : 0); ?> days
	    	</p>
	    	<p>
	    	<label>... by Features</label><?php $this->o($this->project->featureestimate > 0 ? $this->project->featureestimate : 0); ?> days
	    	</p>
	    	<p>
		   	<label>Time Spent</label><?php $this->o($this->project->currenttime > 0 ? $this->project->currenttime : 0); ?> hours
		   	</p>
    	</div>
    	 <div style="clear: left;"></div>
    	<div>
    	<p>
    	<a class="abutton" title="Edit" href="<?php echo build_url('project', 'edit', array('id'=> $this->project->id))?>">
	      	Edit This <?php echo $label ?>
	    </a>
	    <!-- <a class="abutton" title="View Traceability" href="<?php echo build_url('project', 'traceability', array('id'=> $this->project->id))?>">
	      	Trace the <?php echo $label ?>
	    </a> -->
	    
      	</p>
    	</div>

<!-- 
    	<h3>Current Overview</h3>
	  	<?php 
	    	/*$projectOverview = new CompositeView('project/project-overview.php'); 
	        $projectOverview->project = $this->project;
	        echo $projectOverview->render('null');*/
	    ?>
 -->   
 	</div>
 	<div style="clear: left;"></div>
 
 	<div class="bordered">
 		<h3>Free Support Period</h3>
 		<?php if ($this->project->startfgp):?>
	    	<p>
	    	<label>Start Date</label><?php $this->o(date('l dS M, Y', strtotime($this->project->startfgp))); ?>
	    	</p>
	    	<p>
	    	<label>End Date</label><?php $this->o(date('l dS M, Y', $this->project->getFreeSupportEndDate())); ?>
	    	</p>
	    	<p>
	    	<label>Days Left</label><?php $this->o($this->project->getFreeSupportDays()); ?>
	    	</p>
	    <?php else: ?>
	    	<p>not started yet</p>
	    <?php endif; ?>
	</div> 	
	
 	<div style="clear: left;"></div>
 
 	<div class="bordered">
 		<?php if (!$this->project->ismilestone): ?>
 		<h3>Milestones</h3>
 		<?php endif; ?>
 		<p>
 		Show tasks for 
 		<select name="projectuser" id="projusersel">
 			<option value="all">All</option>
 		<?php 
 		$pid = $this->projectuser ? $this->projectuser->id : 0;
		?>
 		<?php foreach ($this->project->getUsers() as $uid => $projectUser): ?>
 			<option value="<?php echo $uid?>" <?php echo $pid == $uid ? "selected='selected'" : ''?>><?php $this->o($projectUser->username)?></option>
 		<?php endforeach; ?>
 		</select>
 		<script type="text/javascript">
 			$().ready(function() {
 				$('#projusersel').change(function() {
 					location.href = '<?php echo build_url('project', 'view', array('id'=>$this->project->id))?>projectuser/'+$(this).val();
 				});
 			});
 		</script>
 		</p>
 		<?php if ($this->project->ismilestone): ?> 
	 		<?php $totalComplete = $this->project->countTasks(1);
	 		$totalTasks = $totalComplete + $this->project->countTasks(0);
	 		$percentageComplete = 0;
	 		if ($totalComplete != 0 && $totalTasks != 0) {
				$percentageComplete = ceil($totalComplete / $totalTasks * 100);
	 		}
	 		?>
	 		<div class="milestone-entry">
	 		<?php $this->percentageBar($percentageComplete, 2, '#0045FF')?>
	 		
	 		<h3>Tasks in this milestone (due <?php $this->o(date('F jS Y', strtotime($this->project->due)))?>, <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> tasks completed)</h3>
	 		<ul class="project-task-summary">
	 		<?php 
	 		$completed = 0;
	 		$estimated = 0;
	 		foreach ($this->project->getOpenTasks($this->projectuser) as $openTask): 
	 			$completed += $openTask->timespent;
	 			$estimated += $openTask->estimated;
	 		?>
	 			<li>
	 			<?php $this->percentageBar($openTask->getPercentage())?>
	 			<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
	 			<?php foreach ($openTask->userid as $username): ?>
	 			<span>[<?php $this->o($username)?>]</span>
	 			<?php endforeach; ?>
	 			<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
	 			</li>
	 		<?php endforeach; ?> 
	 		</ul><br/>
	 		
	 		<p>Time spent on open tasks: <?php $this->o(sprintf('%.2f', ($completed > 0 ? $completed / 3600 : 0))) ?> / <?php $this->o($estimated) ?></p>
	 		</div>
	 	<?php else: // not a milestone ?>
	 		<?php $children = $this->project->getMilestones(); 
	 		foreach ($children as $childProject): ?>
		 		<?php $totalComplete = $childProject->countContainedTasks(1);
		 		$totalTasks = $totalComplete + $childProject->countContainedTasks(0);
				$percentageComplete = 0;
		 		if ($totalComplete != 0 && $totalTasks != 0) {
					$percentageComplete = ceil($totalComplete / $totalTasks * 100);
		 		}
		 		?>
		 		<div class="milestone-entry bordered">
		 		<?php $this->percentageBar($percentageComplete, 2, '#0045FF')?>

		 		<h3><?php $this->o($childProject->title)?> (due <?php $this->o(date('F jS Y', strtotime($childProject->due)))?>, <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> completed)  
		 		<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
				<a href="<?php echo build_url('task', 'edit', array('projectid'=>$childProject->id))?>"><img src="<?php echo resource('images/add.png'); ?>" /></a>
		 		<?php endif; ?>
		 		<a href="<?php echo build_url('project', 'view', array('id'=>$childProject->id))?>">&raquo;</a>
		 		</h3>
		 		
		 		<p>
		 		<?php $this->bbCode($childProject->description) ?>
		 		</p>
		 		
		 		<div>
		 		<h4>Features</h4>
		 		<div class="milestone-feature-listing">
		 			<?php $featureEstimate = 0; ?>
					<ul>
						<?php foreach ($childProject->getFeatures() as $feature): ?>
						<li>
						<?php
						$percentageComplete = $feature->getPercentageComplete();
						$featureEstimate += $feature->estimated;
			        	?>
			        	<?php $this->percentageBar($percentageComplete)?>
			        	(<?php $this->o($feature->estimated); ?>)
			        	<?php if ($feature->complete): ?>
				        	<img class="small-icon" src="<?php echo resource('images/accept.png')?>"></img>
				        <?php endif;?>
			        	<a href="<?php echo build_url('feature', 'edit', array('id' => $feature->id))?>"><?php $this->o($feature->title)?></a>
						</li>
						<?php endforeach; ?>
					</ul>
					<p>Estimated <?php $this->o($featureEstimate) ?> days</p> 
		 		</div>
		 		</div>

				<h4>Tasks</h4>
		 		<ul class="project-task-summary">
		 		<?php 
		 		$completed = 0;
	 			$estimated = 0;
		 		foreach ($childProject->getContainedOpenTasks($this->projectuser) as $openTask): 
		 			$completed += $openTask->timespent;
	 				$estimated += $openTask->estimated;
		 		?>
		 			<li>
		 			<?php $this->percentageBar($openTask->getPercentage())?>
		 			<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
		 			<?php foreach ($openTask->userid as $username): ?>
		 			<span>[<?php $this->o($username)?>]</span>
		 			<?php endforeach; ?>
		 			<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
		 			</li>
		 		<?php endforeach; ?> 
		 		</ul><br/>
		 		<p>Time spent on open tasks: <?php $this->o(sprintf('%.2f', ($completed > 0 ? $completed / 3600 : 0))) ?> / <?php $this->o($estimated) ?></p>
		 		</div>
	 		<?php endforeach; ?>
	 		
	 		<!-- Only allowing projects to have milestones -->
			<form action="<?php echo build_url('project', 'addChild')?>" method="post">
	        	<input type="hidden" name="projectid" value="<?php echo $this->project->id?>" />
	        	<input type="hidden" name="milestone" value="1" />
	        	New Milestone:
	        	<input size="40" type="text" name="newTitle" value="" />
	    		Due:
	    		<input readonly="readonly" type="text" name="due" id="due" value="" size="10" />
	    		<?php $this->calendar('due', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
	        	<input type="submit" class="abutton" value="Create Milestone" />
	        </form>
	 	<?php endif; ?>
	</div>
	
	<?php if (!$this->project->ismilestone): ?>
	<div class="bordered">
		
		<h3>Requests</h3>
	    <?php $this->dispatch('issue', 'projectlist', array('projectid'=>$this->project->id)); ?>
	
	    <p>
		<a class="abutton" href="<?php echo build_url('issue', 'edit', array('projectid'=>$this->project->id))?>">Create Request</a>
	    </p>
	</div>
	<?php endif ; ?>
	
    </div>
<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	<div id="group-users">
		<form action="<?php echo build_url('project', 'updategroup')?>" method="post">
		<input type="hidden" name="id" value="<?php echo $this->project->id?>" />
		<input type="hidden" name="groupid" value="<?php echo $this->group ? $this->group->id : 0 ?>" />
			<p>
			<label for="group-users">Users</label>
			<select name="groupusers[]" multiple="multiple" size="10" id="group-users">
			    <?php 
			    foreach ($this->users as $user): ?>
			    <option value="<?php echo $user->id?>" <?php echo isset($this->groupusers[$user->id]) ? 'selected="selected"' : '';?>><?php echo $this->o($user->getUsername())?></option>
			    <?php endforeach; ?>
			</select>
			</p>
		
			<p>
			<input type="submit" class="abutton" value="Save" />
			</p>
		</form>
    </div>
    
<?php endif; ?>    
	
	
    <?php if ($this->project->ismilestone): ?>
    <div id="tasks">
        <h3>
            Current Tasks
        </h3>
        
        <table class="item-table" cellpadding="0" cellspacing="0">
        <thead>
        	<tr>
        	<th width="40%">Title</th>
        	<th>Due</th>
        	<th>Assigned To</th>
        	<th>Actions</th>
        	</tr>
        </thead>
        <tbody>
        <?php 
    	$taskRowView = new CompositeView('task/task-line-item.php'); 
    	
    	foreach ($this->displayedTasks as $task) { 

            $taskRowView->task = $task;
            $taskRowView->project = $this->project;
            $taskRowView->showActions = true;
            echo $taskRowView->render('null');
    	} ?>
        </tbody>
        </table>
        <?php $this->pager($this->totalTasks, $this->taskListSize, $this->taskPagerName, array('#tasks')); ?>

        <script type="text/javascript">
			$().ready(function() {
				var taskIndex = $("#tasks-index");
				if (taskIndex) {
					taskIndex.html("Tasks (<?php echo count($this->displayedTasks)?>)");
				}
			});
			
function showChart()
{
	var chartContainer = $('#chart-container');
	if (chartContainer.length == 0) {
		$('body').append('<div id="chart-container">no<br/>really</div><div id="chart-content">Some chart content</div>');
		chartContainer = $('#chart-container');
	}
	chartContainer.click(function() {
		$(this).remove();
		$('#chart-content').remove();
	});

	$(window).resize(positionChart);
	$(window).scroll(positionChart);
}

function positionChart()
{
	var chartContent = $('#chart-content');
	if (chartContent.length != 0) {
		alert(window.scrollY);
	}
}
		</script>

		<form action="<?php echo build_url('task', 'import')?>" method="post" enctype="multipart/form-data">
		<p>
		<!-- <a class="abutton" href="#" onclick="$('#task-export-panel').toggle(); return false;">Export</a>
		 -->
		 <!-- only shown if this is a milestone -->
		
		<a class="abutton" href="<?php echo build_url('task', 'edit', array('projectid'=>$this->project->id))?>">Add Task</a>
		<a class="abutton" href="<?php echo build_url('project', 'chart', array('id'=>$this->project->id))?>">View Chart</a>
		
		</p>
		<!-- 
		<p>
		<input type="file" name="importfile" />
		GanttProject <input type="radio" name="importtype" value="gp" />
		MS Project  <input type="radio" name="importtype" value="ms" />
		<input type="hidden" name="id" value="<?php echo $this->project->id ?>"/>
		<input type="submit" value="Import" class="abutton" />
        </p> 
        -->
		</form>
		
		<!--
		<div id="task-export-panel" style="display: none">
			<form method="post" action="<?php echo build_url('task', 'export')?>">
			<input type="hidden" name="id" value="<?php echo $this->project->id ?>"/>
			<p>
			Include completed tasks?
			<input type="checkbox" name="includecompleted" value="1" />
			From <input size="7" readonly="readonly" type="text" class="input" name="exportfrom" id="exportfrom" />
		    <?php $this->calendar('exportfrom', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
			To <input size="7" readonly="readonly" type="text" class="input" name="exportto" id="exportto" />
		    <?php $this->calendar('exportto', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    
		    GanttProject <input type="radio" name="importtype" value="gp" />
			MS Project  <input type="radio" name="importtype" value="ms" />
		
			<input type="submit" class="abutton" value="Go" />
			</p>
			</form>
		</div>
		-->

        
        <h3>
            Completed Tasks
        </h3>
        <table class="item-table" cellpadding="0" cellspacing="0">
        <thead>
        	<tr>
        	<th width="40%">Title</th>
        	<th>Due</th>
        	<th>Assigned To</th>
        	<th>Actions</th>
        	</tr>
        </thead>
        <tbody>
        <?php 
    	$taskRowView = new CompositeView('task/task-line-item.php'); 
    	
    	foreach ($this->completedTasks as $task) { 

            $taskRowView->task = $task;
            $taskRowView->project = $this->project;
            echo $taskRowView->render('null');
    	} ?>
        </tbody>
        </table>
        
        <?php $this->pager($this->totalCompleted, $this->taskListSize, $this->completedPagerName, array('#tasks')); ?>
        
    </div>
    <?php else: ?>
    
    <div id="tasks">
        <h3>
            Current Tasks
        </h3>
        
        <table class="item-table" cellpadding="0" cellspacing="0">
        <thead>
        	<tr>
        	<th width="40%">Title</th>
        	<th>Due</th>
        	<th>Assigned To</th>
        	<th>Actions</th>
        	</tr>
        </thead>
        <tbody>
        <?php 
    	$taskRowView = new CompositeView('task/task-line-item.php'); 
    	
    	foreach ($this->displayedTasks as $task) { 

            $taskRowView->task = $task;
            $taskRowView->project = $this->project;
            $taskRowView->showActions = true;
            echo $taskRowView->render('null');
    	} ?>
        </tbody>
        </table>
        <?php $this->pager($this->totalTasks, $this->taskListSize, $this->taskPagerName, array('#tasks')); ?>

        <script type="text/javascript">
			$().ready(function() {
				var taskIndex = $("#tasks-index");
				if (taskIndex) {
					taskIndex.html("Tasks (<?php echo count($this->displayedTasks)?>)");
				}
			});
			
function showChart()
{
	var chartContainer = $('#chart-container');
	if (chartContainer.length == 0) {
		$('body').append('<div id="chart-container">no<br/>really</div><div id="chart-content">Some chart content</div>');
		chartContainer = $('#chart-container');
	}
	chartContainer.click(function() {
		$(this).remove();
		$('#chart-content').remove();
	});

	$(window).resize(positionChart);
	$(window).scroll(positionChart);
}

function positionChart()
{
	var chartContent = $('#chart-content');
	if (chartContent.length != 0) {
		alert(window.scrollY);
	}
}
		</script>

		<form action="<?php echo build_url('task', 'import')?>" method="post" enctype="multipart/form-data">
		<p>
		<!-- <a class="abutton" href="#" onclick="$('#task-export-panel').toggle(); return false;">Export</a>
		 -->
		 <!-- only shown if this is doesn't have milestones -->
		
		</p>
		<!-- 
		<p>
		<input type="file" name="importfile" />
		GanttProject <input type="radio" name="importtype" value="gp" />
		MS Project  <input type="radio" name="importtype" value="ms" />
		<input type="hidden" name="id" value="<?php echo $this->project->id ?>"/>
		<input type="submit" value="Import" class="abutton" />
        </p> 
        -->
		</form>
		
		<!--
		<div id="task-export-panel" style="display: none">
			<form method="post" action="<?php echo build_url('task', 'export')?>">
			<input type="hidden" name="id" value="<?php echo $this->project->id ?>"/>
			<p>
			Include completed tasks?
			<input type="checkbox" name="includecompleted" value="1" />
			From <input size="7" readonly="readonly" type="text" class="input" name="exportfrom" id="exportfrom" />
		    <?php $this->calendar('exportfrom', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
			To <input size="7" readonly="readonly" type="text" class="input" name="exportto" id="exportto" />
		    <?php $this->calendar('exportto', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    
		    GanttProject <input type="radio" name="importtype" value="gp" />
			MS Project  <input type="radio" name="importtype" value="ms" />
		
			<input type="submit" class="abutton" value="Go" />
			</p>
			</form>
		</div>
		-->

        
        <h3>
            Completed Tasks
        </h3>
        <table class="item-table" cellpadding="0" cellspacing="0">
        <thead>
        	<tr>
        	<th width="40%">Title</th>
        	<th>Due</th>
        	<th>Assigned To</th>
        	<th>Actions</th>
        	</tr>
        </thead>
        <tbody>
        <?php 
    	$taskRowView = new CompositeView('task/task-line-item.php'); 
    	
    	foreach ($this->completedTasks as $task) { 

            $taskRowView->task = $task;
            $taskRowView->project = $this->project;
            echo $taskRowView->render('null');
    	} ?>
        </tbody>
        </table>
        
        <?php $this->pager($this->totalCompleted, $this->taskListSize, $this->completedPagerName, array('#tasks')); ?>
        
    </div>
    
    <div id="status">
    	<h3>Status Reports</h3>
    	<div>
    		<table class="item-table" cellpadding="0" cellspacing="0">
	        <thead>
	        	<tr>
	        	<th>Title</th>
	        	<th>Date generated</th>
	        	<th width="20%">Actions</th>
	        	</tr>
	        </thead>
	        <tbody>
	        	<?php foreach ($this->projectStatusReports as $report): ?>
	        		<tr>
	        			<td>
						<a href="<?php echo build_url('project', 'editreport', array('id'=>$report->id));?>">
						<?php $this->o($report->title) ?>
						</a>
	        			</td>
	        			<td>
	        			<?php $this->o($this->u()->wordedDate($report->dategenerated))?> 
	        			</td>
	        			<td>
	        			<a title="View HTML Report" target="_blank" href="<?php echo build_url('project', 'status', array('id'=>$this->project->id, 'projectstatus' => $report->id))?>"><img class="small-icon" src="<?php echo resource('images/eye.png')?>" /></a>
	        			<a title="View PDF Report" target="_blank" href="<?php echo build_url('project', 'status', array('id'=>$this->project->id, 'projectstatus' => $report->id, 'pdf' => 1))?>"><img class="small-icon" src="<?php echo resource('images/adobe.png')?>" /></a>
	        			<a title="Delete Report" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('project', 'deletestatusreport', array('id' => $report->id))?>'; return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
	        			</td>
	        		</tr>
	        	<?php endforeach; ?>
	        </tbody>
	        </table>
    		<p>
    		<a class="abutton" title="Add new report" href="<?php echo build_url('project', 'editreport', array('projectid'=>$this->project->id))?>">
    			Add New Report
    		</a>
    		</p>
    	</div>
		
    </div>
    
    <div id="sub-projects">
        <?php $this->dispatch('project', 'childProjects', array('projectid'=> $this->project->id)); ?>
        <p>
        <form action="<?php echo build_url('project', 'addChild')?>" method="post">
        	<input type="hidden" name="projectid" value="<?php echo $this->project->id?>" />
        	<input size="40" type="text" name="newTitle" value="Sub project of <?php $this->o($this->project->title)?>" />
        	<input type="submit" class="abutton" value="Create Sub Project" />
        </form>
        </p>
    </div>
    
    <div id="timesheet">
        <?php $this->dispatch('timesheet', 'list', array('projectid'=> $this->project->id)); ?>
        <p>
            <a class="abutton" href="<?php echo build_url('timesheet', 'edit', array('projectid'=>$this->project->id))?>">Add Timesheet</a>
            <a class="abutton" href="<?php echo build_url('timesheet', 'index', array('projectid'=>$this->project->id))?>">View Times</a>
        </p>
    </div>
    
	
	<div id="features">
	    <form action="<?php echo build_url('feature', 'createtasks')?>" method="post">
	    <input type="hidden" name="projectid" value="<?php echo $this->project->id?>" />
	    <div id="project-info-<?php echo $this->project->id?>-feature">
	        <?php $this->dispatch('feature', 'projectlist', array('projectid'=>$this->project->id)); ?>
	    </div>
	    <p>
	    <input style="float: right;" class="abutton" type="submit" value="Create Tasks" />
	    <a class="abutton" title="Add Feature" href="<?php echo build_url('feature', 'edit', array('projectid'=>$this->project->id))?>">Add Feature</a>
	    <a class="abutton" title="Recalculate Project Estimate" href="<?php echo build_url('feature', 'recalculate', array('projectid'=>$this->project->id))?>">Calculate Cost</a>
	
	    </p>
	    </form>
	</div>
	
    <div id="files">
	    <div>
	    <?php $this->dispatch('project', 'filelist', array('projectid'=>$this->project->id), null, array('folder')); ?>
	    </div>
	</div>
    <?php endif; ?>
    
</div>