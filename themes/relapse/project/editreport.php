<?php if ($this->project->id): ?>
    <div id="parent-links" class="std">
        <a title="Back to Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
    </div>
<?php endif; ?>

<div class="std">
<h2><?php $this->o($this->model->title)?></h2>

<form method="post" action="<?php echo build_url('project', 'savereport');?>" class="data-form">
<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
<?php if ($this->model->id): ?>
    <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
<div class="wide-form"> 
	<?php $this->textInput('Title', 'title') ?>
    <?php $this->textInput('Summary', 'completednotes', true) ?>
    <?php $this->textInput('Todo', 'todonotes', true) ?>
    <?php $this->selectList('Milestone', 'milestone', $this->project->getMilestones(), '', 'id', 'title', false, false); ?>
</div>

<div class="inner-column">
	<p>
	    <label for="due">From:</label>
	    <input readonly="readonly" type="text" class="input" name="startdate" id="startdate" value="<?php echo $this->model->startdate ? date('Y-m-d', strtotime($this->model->startdate)) : date('Y-m-d', time())?>" />
	    <?php $this->calendar('startdate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
</div>

<div class="inner-column">
	<p>
	    <label for="due">To:</label>
	    <input readonly="readonly" type="text" class="input" name="enddate" id="enddate" value="<?php echo $this->model->enddate ? date('Y-m-d', strtotime($this->model->enddate)) : date('Y-m-d', time() + 86400)?>" />
	    <?php $this->calendar('enddate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
</div>

<p class="clear">
    <input type="submit" class="abutton wymupdate" value="Save" accesskey="s" />
</p>
</form>
</div>

<div class="std">
<?php if ($this->model->id): ?>
<form class="data-form" method="post" action="<?php echo build_url('project', 'generatereport');?>" onsubmit="return confirm('Are you sure you want to regenerate the report?')">
<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<input type="submit" class="abutton" value="Generate Report" accesskey="g" />
<a href="<?php echo build_url('project', 'status', array('id' => $this->project->id, 'projectstatus' => $this->model->id))?>" class="abutton">View</a>
</form>
<?php endif; ?>

    <?php if ($this->model->id) { 
	    $view = new CompositeView();
	    $view->project = $this->project;
	    $view->status = $this->model;
	    $content = $view->render('project/displaystatus.php');
	    echo $content;
    }
    ?>
</div>