<script type="text/javascript">
    $().ready(function() {
        $("select#clientid").change(function(){
        	$('#projectSelector-parentid').load('<?php echo build_url('project', 'projectSelector')?>', {empty: '1', clientid: $(this).val(), selectorType: 'project', fieldName: 'projectid'});
		});
    });
</script>

<?php if ($this->model->id): ?>
    <div id="parent-links">
        <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->client->id));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
    </div>
<?php endif; ?>

<h2><?php $this->o($this->model->title)?></h2>

<form method="post" action="<?php echo build_url('project', 'save');?>">
<input type="hidden" value="<?php echo $this->client->id?>" name="clientid" />
<?php if ($this->model->id): ?>
    <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
	<div class="wide-form">
	<?php $this->textInput('Title', 'title') ?>
	<?php $this->textInput('Description', 'description', true); ?>
	</div>
<div class="inner-column">
	
    <?php $this->textInput('Project URL', 'url') ?>
    <?php $this->textInput('SVN URL', 'svnurl') ?>
    <?php $this->yesNoInput('Private?', 'isprivate'); ?>
    <?php if ($this->model->parentid): // If this project has a parent, we can make it a milestone instead ?>
    <?php $this->yesNoInput('Milestone?', 'ismilestone'); ?>
    <?php endif; ?>
    <?php $this->yesNoInput('Auto Reports?', 'enablereports'); ?>
    
    <p>
    <label for="started">Start Date:</label>
    <input readonly="readonly" type="text" class="input" name="started" id="started" value="<?php echo $this->model->started?>" />
    <?php $this->calendar('started', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    
    <p>
    <label for="actualstarted">Actual Start:</label>
    <?php if ($this->model->actualstart): ?>
    <?php $this->o(date('F jS Y', strtotime($this->model->actualstart))); ?>
    <!-- Hacked in so that it comes through on the other side... -->
    <input type="hidden" value="<?php $this->o($this->model->actualstart)?>" name="actualstart"></input>
    <?php else: ?>
    &nbsp;
    <?php endif; ?>
    </p>
    
    <p>
    <label for="due">Due:</label>
    <input readonly="readonly" type="text" class="input" name="due" id="due" value="<?php echo $this->model->due?>" />
    <?php $this->calendar('due', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>

    <p>
    <label for="completed">Actual Completion:</label>
    <input readonly="readonly" type="text" class="input" name="completed" id="completed" value="<?php echo $this->model->completed?>" />
    <?php $this->calendar('completed', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    
    <p>
    <label for="invoiceissued">Invoice Issued:</label>
    <input readonly="readonly" type="text" class="input" name="invoiceissued" id="invoiceissued" value="<?php echo $this->model->invoiceissued?>" />
    <?php $this->calendar('invoiceissued', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    <p>
    <label for="startfgp">Start Guarantee:</label>
    <input readonly="readonly" type="text" class="input" name="startfgp" id="startfgp" value="<?php echo $this->model->startfgp?>" />
    <?php $this->calendar('startfgp', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
    </p>
    <p>
    <?php $this->textInput('Guarantee Days', 'durationfgp') ?>
    </p>
</div>
<div class="inner-column">
	<?php $this->textInput('Budgeted hours', 'budgeted', false, 'size="4"') ?>
	
	<?php $this->textInput('Estimated hours', 'estimated', false, 'size="4"') ?>
	<p>
   	<label>... by Tasks</label><?php $this->o($this->model->taskestimate > 0 ? $this->model->taskestimate : 0); ?> days
   	</p>
   	<p>
   	<label>... by Features</label><?php $this->o($this->model->featureestimate > 0 ? $this->model->featureestimate : 0); ?> days
   	</p>
   	<p>
   	<label>Time Spent</label><?php $this->o($this->model->currenttime > 0 ? $this->model->currenttime : 0); ?> hours
   	</p>
	<?php $this->priceInput('Hourly Rate', 'rate'); ?>
	
	<p>
    <label for="ownerid">Assigned To:</label>
    <select name="ownerid" id="ownerid">
        <option value='0'></option>    
    <?php 
        $selUser = $this->model->ownerid ? $this->model->ownerid : '';
        foreach ($this->owners as $owner): ?>
            <option value="<?php echo $owner->id?>" <?php echo $selUser == $owner->id ? 'selected="selected"' : '';?>><?php $this->o($owner->title)?></option>
        <?php endforeach; ?>
    </select>
    </p>
    <p>
    <label for="manager">Manager:</label>
    <select name="manager" id="manager">
        <option value=' '></option>    
    <?php 
        $selUser = $this->model->manager ? $this->model->manager : '';
        foreach ($this->users as $user): ?>
            <option value="<?php echo $user->getUsername()?>" <?php echo $selUser == $user->getUsername() ? 'selected="selected"' : '';?>><?php $this->o($user->getUsername())?></option>
        <?php endforeach; ?>
    </select>
    </p>
    
    <p>
    <label for="clientid">Client:</label>
    <select name="clientid" id="clientid">
        <?php 
        $sel = $this->model->clientid ? $this->model->clientid : $this->client->id;
        foreach ($this->clients as $client): ?>
            <option value="<?php echo $client->id?>" <?php echo $sel == $client->id ? 'selected="selected"' : '';?>><?php $this->o($client->title);?></option>
        <?php endforeach; ?>
    </select>
    </p>
    
    <p>
    <label for="parentid">Parent Project:</label>
    <?php $this->projectSelector('parentid', $this->projects, 'project', true) ?>
    </p>
    
</div>
<p class="clear">
    <input type="submit" class="abutton" value="Save" accesskey="s" />
    <input type="button" class="abutton" value="Delete" onclick="$('#delete-panel').show()"/>
    <?php if ($this->model->id): ?>
	    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'view', array('id' => $this->model->id))?>'" value="Close" />
	    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'recalculate', array('id' => $this->model->id))?>'" value="Calculate Estimates" />    
    <?php else: ?>
	    <input type="button" class="abutton" onclick="history.go(-1);" value="Close" />
    <?php endif; ?>
</p>
</form>

<?php if ($this->model->id): ?>
<div id="delete-panel" style="display: none;">
<form method="post" action="<?php echo build_url('project', 'delete')?>">
	<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
	<p>Select a new parent for all tasks, issues and milestones to be moved to. </p>
	<select name="parentid" id="parentid">
		<option value="0"> </option>
        <?php 
        foreach ($this->projects as $project): ?>
        	<?php if ($this->model->id == $project->id || $this->model->id == $project->parentid) continue; ?>
            <option value="<?php echo $project->id?>"><?php $this->o($project->title);?></option>
        <?php endforeach; ?>
    </select>
    
    <input type="submit" class="abutton warning" value="Delete" />
</form>
</div>
<?php endif; ?>