<?php if ($this->project->id): ?>
<div id="parent-links">
	<?php if ($this->project->parentid): ?>
	<a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->parentid));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
	<?php endif; ?>
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->project->clientid));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
</div>
<?php endif; ?>

<h2><?php $this->o($this->project->title)?></h2>

<div id="project-container">
    <ul class="tab-options">
    	<li><a href="#overview"><span>Overview</span></a></li>
    	<?php if (!$this->project->ismilestone): ?> 
    	<li><a href="#sub-projects"><span id="sub-projects-index">Sub Projects</span></a></li>
        <li><a href="#files"><span>Files</span></a></li>
        <?php endif; ?>
    </ul>
	<div id="overview">
		<div class="bordered">
		<h3>Details</h3>
    	<div class="inner-column">
	    	<p>
	    	<label>Start Date</label><?php $this->o($this->u()->wordedDate($this->project->started)); ?>
	    	</p>
	    	<p>
	    	<label>Due Date</label>
	    	<?php $this->o($this->project->due ? $this->u()->wordedDate($this->project->due) : '&nbsp;'); ?>
	    	</p>
    	</div>
    	<div class="inner-column">
    		<p>
		    <label for="actualstarted">Actual Start:</label>
		    <?php if ($this->project->actualstart): ?>
		    <?php $this->o($this->u()->wordedDate($this->project->actualstart)); ?>
		    <?php else: ?>
		    &nbsp;
		    <?php endif; ?>
		    </p>
    	</div>
    	
    	<div class="clear"></div>
    	
    	<div style="clear: left;"></div>
 	<!-- //end bordered -->
	</div>
		
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
		
		<?php 
		$children = $this->project->getMilestones(); 
		if (!$this->project->ismilestone && count($children)): ?>
	 	<div class="bordered">
	 		<h3>Milestones</h3>
	 		
	 		<?php 
	 		foreach ($children as $childProject): ?>
		 		<?php $totalComplete = $childProject->countContainedTasks(1);
		 		$totalTasks = $totalComplete + $childProject->countContainedTasks(0);
				$percentageComplete = 0;
		 		if ($totalComplete != 0 && $totalTasks != 0) {
					$percentageComplete = ceil($totalComplete / $totalTasks * 100);
		 		}
		 		
		 		$started = "Not Started";
		 		// Figure out whether this project has started / is complete
		 		$started = $childProject->hasStarted() ? "In Progress" : $started;
		 		$started = $childProject->isComplete() ? "Completed ".$this->u()->wordedDate($childProject->completed) : $started;
		 		
		 		// go through all contained tasks to get a total, then again again for
                   // actually printing stuff out
                   
		 		?>
		 		<div class="milestone-entry">
				<div style="float: right; font-size: 1.2em; font-weight: bold; border-bottom: 1px dashed green;"><?php $this->o($started)?></div>
		 		<h3><?php $this->o($childProject->title)?> (due <?php $this->o($this->u()->wordedDate($childProject->due))?><!-- , <?php $this->o($totalComplete)?> of <?php $this->o($totalTasks)?> completed -->)  <a href="<?php echo build_url('project', 'view', array('id'=>$childProject->id))?>">&raquo;</a></h3>
				
		 		<br/>
		 		</div>
	 		<?php endforeach; ?>
	 	</div>
	 	<?php endif; ?>

	 	<?php if (!$this->project->ismilestone): ?>
	 	<div class="bordered">
	 		<h3>Requests</h3>
	 		<div id="project-info-<?php echo $this->project->id?>-issue">
		    <?php $this->dispatch('issue', 'projectlist', array('projectid'=>$this->project->id)); ?>
		    </div>
		    <p>
			<a class="abutton" href="<?php echo build_url('issue', 'edit', array('projectid'=>$this->project->id))?>">Create Request</a>
		    </p>
	 	</div>
	 	<?php endif; ?>

		
	<!-- //overview -->
	</div>

	<?php if (!$this->project->ismilestone): ?>
    <div id="files">
	    <div>
	    <?php $this->dispatch('project', 'filelist', array('projectid'=>$this->project->id), null, array('folder')); ?>
	    </div>
	</div>
	<div id="sub-projects">
        <?php $this->dispatch('project', 'childProjects', array('projectid'=> $this->project->id)); ?>
        <p>
        
        </p>
    </div>
    <?php endif; ?>
</div>


<script type="text/javascript">
    $().ready(function(){
        $("#project-container").tabs();
    });
</script>