
<?php $label = $this->project->ismilestone ? 'Milestone' : 'Project';?>

<?php if ($this->project->id): ?>
<div id="parent-links">
	<?php if ($this->project->parentid): ?>
	<a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->parentid));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
	<?php endif; ?>
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->project->clientid));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
</div>
<?php endif; ?>

<h2><?php $this->o($this->project->title.' (#'.$this->project->id.')')?></h2>

<div id="project-container">
    <ul class="tab-options">
    	<li><a href="#overview"><span>Overview</span></a></li>
    	<?php if ($this->u()->isPower()): ?>
    	<li><a href="#group-users"><span>Users</span></a></li>
    	<?php endif; ?>
    	<li><a href="#status"><span>Status</span></a></li>
    	<li><a href="#sub-projects"><span id="sub-projects-index">Sub Projects</span></a></li>
        <li><a href="#timesheet"><span>Timesheets</span></a></li>
        <li><a href="#tasks"><span id="tasks-index">Tasks</span></a></li>
        <?php $this->getMods($this, 'project-view-index');?>
    </ul>

    <div id="overview">
    	<h3>Details</h3>
    	<div class="inner-column">
	    	<p>
	    	<label>Start Date</label><?php $this->o(date('Y-m-d', strtotime($this->project->started))); ?></a>
	    	</p>
	    	<p>
	    	<label>Due Date</label><?php $this->o($this->project->due ? date('Y-m-d', strtotime($this->project->due)) : '_'); ?></a>
	    	</p>
	    	<p>
	    	<label>Estimated Hours</label><?php $this->o($this->project->estimated); ?></a>
	    	</p>
    	</div>
    	<div class="inner-column">
			<p>
	    	<label>Website</label><a href="<?php $this->o($this->project->url); ?>"><?php $this->o($this->project->url); ?></a>
	    	</p>
    	</div>
    	 <div style="clear: left;"></div>
    	<div>
    	<p>
    	<a class="abutton" title="Edit" href="<?php echo build_url('project', 'edit', array('clientid' => $this->project->clientid, 'id'=> $this->project->id))?>">
	      	Edit This <?php echo $label ?>
	    </a>
	    <a class="abutton" title="View Traceability" href="<?php echo build_url('project', 'traceability', array('id'=> $this->project->id))?>">
	      	Trace the <?php echo $label ?>
	    </a>
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
 <div style="clear: left;"></div>
 		<hr/>
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
	 		<div style="float: right; width: 200px; background-color: #AF0A30; height: 20px;">
	 			<div style="height: 20px; background-color: #00CF1C; width: <?php echo (2 * ($percentageComplete > 100 ? 100 : $percentageComplete))?>px;"></div>
			</div>
	 		<h3>Tasks in this milestone (due <?php $this->o(date('F jS Y', strtotime($this->project->due)))?>, <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> completed)</h3>
	 		<ul class="project-task-summary">
	 		<?php foreach ($this->project->getOpenTasks($this->projectuser) as $openTask): ?>
	 			<li>
	 			<div style="float: right; width: 100px; background-color: #AF0A30; height: 10px;">
	 			<div style="height: 10px; background-color: #00CF1C; width: <?php echo $openTask->getPercentage() > 100 ? 100 : $openTask->getPercentage()?>px;"></div>
	 			</div>
	 			<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
	 			<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
	 			</li>
	 		<?php endforeach; ?> 
	 		</ul><br/>
	 		</div>
	 	<?php else: ?>
	 		<?php $children = $this->project->getMilestones(); 
	 		foreach ($children as $childProject): ?>
		 		<?php $totalComplete = $childProject->countContainedTasks(1);
		 		$totalTasks = $totalComplete + $childProject->countContainedTasks(0);
				$percentageComplete = 0;
		 		if ($totalComplete != 0 && $totalTasks != 0) {
					$percentageComplete = ceil($totalComplete / $totalTasks * 100);
		 		}
		 		?>
		 		<div class="milestone-entry">
				<div style="float: right; width: 200px; background-color: #AF0A30; height: 20px;">
		 			<div style="height: 20px; background-color: #00CF1C; width: <?php echo (2 * ($percentageComplete > 100 ? 100 : $percentageComplete))?>px;"></div>
				</div>
		 		<h3><?php $this->o($childProject->title)?> (due <?php $this->o(date('F jS Y', strtotime($childProject->due)))?>, <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> completed)  <a href="<?php echo build_url('project', 'view', array('id'=>$childProject->id))?>">&raquo;</a></h3>
			 		
		 		<ul class="project-task-summary">
		 		<?php foreach ($childProject->getContainedOpenTasks($this->projectuser) as $openTask): ?>
		 			<li>
		 			<div style="float: right; width: 100px; background-color: #AF0A30; height: 10px;">
		 			<div style="height: 10px; background-color: #00CF1C; width: <?php echo $openTask->getPercentage() > 100 ? 100 : $openTask->getPercentage()?>px;"></div>
		 			</div>
		 			<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
		 			<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
		 			</li>
		 		<?php endforeach; ?> 
		 		</ul><br/>
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
<?php if ($this->u()->isPower()): ?>
	<div id="group-users">
    	<?php $this->dispatch('project', 'projectGroup', array('id'=> $this->project->id)); ?>
    </div>
    
<?php endif; ?>    

    
    <div id="status">
    	<h3>Status Reports</h3>
    	<div>
    		<table class="item-table" cellpadding="0" cellspacing="0">
	        <thead>
	        	<tr>
	        	<th>Title</th>
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
		<?php if (!$this->project->hasMilestones()) : ?> 
		<a class="abutton" href="<?php echo build_url('task', 'edit', array('projectid'=>$this->project->id))?>">Add Task</a>
		<a class="abutton" href="<?php echo build_url('project', 'chart', array('id'=>$this->project->id))?>">View Chart</a>
		<?php endif; ?>
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
    
    <?php $this->getMods($this, 'project-view');?>
    
</div>

<script type="text/javascript">
    $().ready(function(){
        $("#project-container").tabs({		    
		    onShow: function(tab, content, oldcontent) {
		    	// get the item's child with a class of 'load-target' and use its name attribute to 
		    	// load into it
		    	var targetHolder = $(content).find('.load-target');
		    	if (targetHolder.length > 0 && targetHolder.html().length == 0) {
		    		var url = $(targetHolder).attr('name');
		    		$(targetHolder).load(url);
		    	}
		    }
		});
		
    });
</script>