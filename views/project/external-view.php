<?php if ($this->project->id): ?>
<div id="parent-links">
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->project->clientid));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
</div>
<?php endif; ?>

<h2><?php $this->o($this->project->title)?></h2>

<div id="project-container">
    <ul class="tab-options">
    	<li><a href="#overview"><span>Overview</span></a></li>
    	<li><a href="#issues"><span>Issues</span></a></li>
        <li><a href="#files"><span>Files</span></a></li>
   	    <li><a href="#sub-projects"><span id="sub-projects-index">Sub Projects</span></a></li>
    </ul>

	<div id="overview">
		<h3>Details</h3>
    	<div class="inner-column">
	    	<p>
	    	<label>Start Date</label><?php $this->o(date('Y-m-d', strtotime($this->project->started))); ?></a>
	    	</p>
	    	<p>
	    	<label>Due Date</label><?php $this->o($this->project->due ? date('Y-m-d', strtotime($this->project->due)) : '&nbsp;'); ?></a>
	    	</p>
	    	<p>
	    	<label>Estimated Hours</label><?php $this->o($this->project->estimated); ?></a>
	    	</p>
    	</div>
    	<div class="inner-column">
 		<?php if ($this->project->startfgp):?>
 			<h3>Free Support Guarantee Period</h3>
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
    	<div class="clear"></div>
	</div>
	

    <div id="issues">
	    <div id="project-info-<?php echo $this->project->id?>-issue">
	    <?php $this->dispatch('issue', 'projectlist', array('projectid'=>$this->project->id)); ?>
	    </div>
	    <p>
		<a class="abutton" href="<?php echo build_url('issue', 'edit', array('projectid'=>$this->project->id))?>">Create Request</a>
	    </p>
	</div>
    <div id="files">
	    <div>
	    <?php $this->dispatch('project', 'filelist', array('projectid'=>$this->project->id)); ?>
	    </div>
	</div>
	<div id="sub-projects">
        <?php $this->dispatch('project', 'childProjects', array('projectid'=> $this->project->id)); ?>
        <p>
        
        </p>
    </div>
</div>


<script type="text/javascript">
    $().ready(function(){
        $("#project-container").tabs();
    });
</script>