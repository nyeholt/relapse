<script type="text/javascript">
    $().ready(function() {
        $("select#clientid").change(function(){
        	$('#projectSelector-parentid').load('<?php echo build_url('project', 'projectSelector')?>', {empty: '1', clientid: $(this).val(), selectorType: 'project', fieldName: 'projectid'});
		});
    });
</script>

<form method="post" action="<?php echo build_url('project', 'save');?>" class="data-form">

<?php if ($this->viaajax): ?>
<input type="hidden" value="1" name="_ajax" />
<?php endif; ?>
<?php if ($this->model->id): ?>
    <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
	<fieldset>
		<legend>Info</legend>
		<?php $this->textInput('Title', 'title') ?>
		<?php $this->textInput('Description', 'description', true); ?>
		<?php $this->selectList('Client', 'clientid', $this->clients) ?>
		<?php if ($this->model->parentid): // If this project has a parent, we can make it a milestone instead ?>
			<?php $this->yesNoInput('Milestone?', 'ismilestone'); ?>
		<?php endif; ?>
		<?php $this->yesNoInput('Private?', 'isprivate'); ?>
		<p>
			<label for="parentid">Parent Project:</label>
			<?php $this->projectSelector('parentid', $this->projects, 'project', true) ?>
		</p>
	</fieldset>

	<?php if (!$this->model->ismilestone): ?>
	<fieldset>
		<legend>Times</legend>
		<?php $this->priceInput('Hourly Rate', 'rate'); ?>
		<?php $this->textInput('Budgeted hours', 'budgeted', false, 'size="4"') ?>
		<?php $this->textInput('Estimated hours', 'estimated', false, 'size="4"') ?>
		<!--
		<p>
		<label>... by Tasks</label><?php $this->o($this->model->taskestimate > 0 ? $this->model->taskestimate : 0); ?> days
		</p>-->
		<p>
		<label>Feature Estimate</label><?php $this->o($this->model->featureestimate > 0 ? $this->model->featureestimate : 0); ?> days
		</p>
		<p>
		<label>Current Time Spent</label><?php $this->o($this->model->currenttime > 0 ? $this->model->currenttime : 0); ?> hours
		</p>
	</fieldset>
	<?php endif; ?>

	<fieldset>
		<legend>Dates</legend>
		<?php if (!$this->model->ismilestone): ?>
		<p>
		<label for="actualstarted">Actual Start:</label>
		<?php if ($this->model->actualstart): ?>
		<?php $this->o(date('F jS Y', strtotime($this->model->actualstart))); ?>
		<!-- Hacked in so that it comes through on the other side... -->
		<input type="hidden" value="<?php $this->o($this->model->actualstart)?>" name="actualstart" />
		<?php else: ?>
		&nbsp;
		<?php endif; ?>
		</p>
		<?php endif; ?>

		<?php $this->calendarInput('projectdue', 'Due', 'due'); ?>
		<?php $this->calendarInput('projectcompleted', 'Actual Completion', 'completed'); ?>

		<?php if (!$this->model->ismilestone): ?>
		<?php $this->calendarInput('invissued', 'Invoice Issued', 'invoiceissued'); ?>
		
		<?php endif; ?>
	</fieldset>

	<?php if (!$this->model->ismilestone): ?>
	<fieldset>
		<legend>Project Settings</legend>
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

			<?php $this->textInput('Project URL', 'url') ?>
			<?php $this->textInput('Source Control URL', 'svnurl') ?>
			<?php $this->yesNoInput('Auto Reports?', 'enablereports'); ?>

		

		<!--
		<p>
		<label for="startfgp">Start Guarantee:</label>
		<input readonly="readonly" type="text" class="input" name="startfgp" id="startfgp" value="<?php echo $this->model->startfgp?>" />
		<?php $this->calendar('startfgp', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		</p>
		<p>
		<?php $this->textInput('Guarantee Days', 'durationfgp') ?>
		</p>
		-->

	</fieldset>
	<?php endif; ?>

	

	<p class="clear">
		<input type="submit" class="abutton" value="Save" accesskey="s" />
		<input type="button" class="abutton" value="Delete" onclick="$('#delete-panel').show()"/>
		<?php if ($this->model->id && !$this->model->ismilestone): ?>
			<input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'recalculate', array('id' => $this->model->id))?>'" value="Calculate Estimates" />
		<?php endif; ?>

		<?php if ($this->viaajax): ?>
			<input type="button" class="abutton" onclick="$('#projectdialog').simpleDialog('close');" value="Close" />
		<?php elseif ($this->model->id): ?>
			<input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'view', array('id' => $this->model->id))?>'" value="Close" />
		<?php else: ?>
			<input type="button" class="abutton" onclick="location.reload(true);" value="Close" />
		<?php endif; ?>
	</p>
</form>
<?php if ($this->model->id): ?>
<div id="delete-panel" style="display: none;">
<form method="post" action="<?php echo build_url('project', 'delete')?>">
	<?php $this->requestValidator(); ?>
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