
<?php $label = $this->project->ismilestone ? 'Milestone' : 'Project';?>

<?php if ($this->project->id): ?>

<?php
// info for whether to display the 'add to favourites' stuff 
$deleteStyle = isset($this->existingWatch) && $this->existingWatch ? 'inline' : 'none'; 
$addStyle = $deleteStyle == 'inline' ? 'none' : 'inline';
?>

<div class="std">
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
	<?php $this->hierarchy($this->project->getHierarchy()); ?> &raquo; <?php $this->o($this->project->title.' (#'.$this->project->id.')')?>
	<!-- end if has project id -->
	<?php endif; ?>

	<div class="project-tree" id="project-<?php echo $this->project->clientid?>-tree" style="display: none;">
	</div>
</div>

<?php include dirname(__FILE__).'/milestone-list.php'; ?>
<?php include dirname(__FILE__).'/request-list.php'; ?>
<?php // include dirname(__FILE__).'/user-list.php'; ?>



<div id="overview" class="std">
	<h2><?php $this->o($this->project->title.' (#'.$this->project->id.')')?></h2>
	<div class="dataDetail">
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
			<!--<p>
			<label>Estimated Days</label><?php $this->o($this->project->estimated > 0 ? $this->project->estimated : 0); ?>
			</p>
			<p>
			<label>... by Tasks</label><?php $this->o($this->project->taskestimate > 0 ? $this->project->taskestimate : 0); ?> days
			</p>-->
			<p>
			<label>Estimated time</label><?php $this->o($this->project->featureestimate > 0 ? $this->project->featureestimate : 0); ?> days
			</p>
			<p>
			<label>Time Spent</label><?php $this->o($this->project->currenttime > 0 ? $this->project->currenttime : 0); ?> hours
			</p>
		</div>
		<div class="inner-column">
			<ul class="largeList">
				<li>something</li>
				<li>something</li>
				<li>something</li>
				<li>something</li>
			</ul>
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
</div>

<div class="std dataDetail">
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

	<div style="clear: left;"></div>
</div>

    <?php if ($this->project->ismilestone): ?>
    <div class="std">
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
    
    <div class="std" id="status">
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
    
    <div class="std" id="sub-projects">
        <?php $this->dispatch('project', 'childProjects', array('projectid'=> $this->project->id)); ?>
        <div>
        <form action="<?php echo build_url('project', 'addChild')?>" method="post">
        	<input type="hidden" name="projectid" value="<?php echo $this->project->id?>" />
        	<input size="40" type="text" name="newTitle" value="Sub project of <?php $this->o($this->project->title)?>" />
        	<input type="submit" class="abutton" value="Create Sub Project" />
        </form>
        </div>
    </div>
    
    <div class="std" id="timesheet">
        <?php $this->dispatch('timesheet', 'list', array('projectid'=> $this->project->id)); ?>
        <p>
            <a class="abutton" href="<?php echo build_url('timesheet', 'edit', array('projectid'=>$this->project->id))?>">Add Timesheet</a>
            <a class="abutton" href="<?php echo build_url('timesheet', 'index', array('projectid'=>$this->project->id))?>">View Times</a>
        </p>
    </div>
    
	
	<div class="std" id="features">
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
	
    <div class="std" id="files">
	    <div>
	    <?php $this->dispatch('project', 'filelist', array('projectid'=>$this->project->id), null, array('folder')); ?>
	    </div>
	</div>
    <?php endif; ?>