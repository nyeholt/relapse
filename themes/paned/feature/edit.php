

<form method="post" action="<?php echo build_url('feature', 'save');?>" class="ajaxForm data-form">
	<?php $this->requestValidator(); ?>

	<?php if ($this->viaajax): ?>
	<input type="hidden" value="1" name="_ajax" />
	<?php endif; ?>

	<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />

	<?php if (isset($this->parentfeature)): ?>
	<input type="hidden" value="<?php echo $this->parentfeature?>" name="parentfeature" />
	<?php endif;?>

	<?php if ($this->model->id): ?>
	<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
	<?php endif; ?>

	<fieldset>
		<legend>Details</legend>
		<?php $this->textInput('Feature Title', 'title') ?>
		<?php $this->textInput('Estimated effort (days)', 'estimated', false, ' size="4"'); ?>
		<?php $this->textInput('Description', 'description', true); ?>
		
		<?php $this->textInput('Assumptions', 'assumptions', true); ?>
		<?php $this->textInput('Questions', 'questions', true); ?>

		<!--<?php $this->textInput('Implementation Steps', 'implementation', true); ?>
		<?php $this->textInput('Verification Steps', 'verification', true); ?>-->
	</fieldset>
	
	<fieldset>
		<?php $this->selectList('Status', 'status', $this->statuses) ?>
		<?php $this->selectList('Priority', 'priority', $this->priorities) ?>
		<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
			<p>
			<label for="projectid">Project:</label>
			<?php $this->projectSelector('projectid', $this->projects, 'project', false, $this->project->id) ?>
			</p>
			<p>
			<label for="milestone">Target Milestone:</label>
			<?php $this->projectSelector('milestone', $this->projects, 'milestone', true, $this->model->milestone) ?>
			</p>
		<?php endif; ?>
	</fieldset>
	<p class="clear">
		<input type="submit" class="abutton" value="Submit"  />
		<input type="button" class="abutton" onclick="Relapse.closeDialog('featuredialog', this);" value="Close" />
	</p>
</form>
		
<?php if ($this->model->id && $this->u()->hasRole(User::ROLE_USER)): ?>
<fieldset class="data-form">
	<legend>Tasks</legend>
	<ul class="task-summary">
	<?php $estimated = 0; $taken = 0; ?>
	<?php foreach ($this->linkedTasks as $openTask): ?>
		<li>
		<?php $this->percentageBar($openTask->getPercentage())?>
		<a title="Remove link" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('task', 'removeLinkFrom', array('id' => $openTask->id, 'fromid' => $this->model->id, 'fromtype' => 'Feature', 'linktype'=>'to'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a>
		<span style="background-color: <?php echo $openTask->getStalenessColor() ?>" title="Task staleness (blue is older)">&nbsp;&nbsp;</span>
		<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
			<?php if ($openTask->complete): ?>
			<img class="small-icon" src="<?php echo resource('images/accept.png')?>" />
			<?php endif; ?>
			<?php $this->addToPane(build_url('task', 'edit', array('id'=>$openTask->id)), $this->escape($openTask->title), $this->escape($openTask->title), 'RightPane'); ?>
		<?php else: ?>
			<?php $this->o($openTask->title)?>
		<?php endif; ?>
		<?php $estimated += $openTask->estimated; $taken += $openTask->timespent; ?>
		</li>
	<?php endforeach; ?>
		<li>
			<p>Time taken: <?php $this->o(sprintf("%.2f", $taken > 0 ? $taken / 3600 : 0)) ?> / <?php $this->o($estimated) ?> hours</p>
		</li>
	</ul>
	<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	<p>
		<?php $this->addToPane(build_url('task', 'linkedtaskform', array('id' => $this->model->id, 'type'=> 'Feature')), 'Add New Task', 'Add New Task', 'RightPane'); ?>
	</p>
	<?php endif; ?>
</fieldset>

<?php endif; ?>


<?php if ($this->model->id && $this->u()->hasRole(User::ROLE_USER)): ?>
<fieldset>
	<legend>Other Features</legend>
	<div>
		<h3>Parents</h3>
		<ul class="linkedItemList">
			<?php foreach ($this->linkedFromFeatures as $feature): ?>
			<li>
				<a title="Remove feature" href="#" onclick="if (confirm('Are you sure?')) { var link = this;  $.post('<?php echo build_url('feature', 'removeFeature', array('_ajax' => 1, 'id' => $this->model->id, 'featureid'=>$feature->id, 'linktype'=>'to'))?>', {}, function (data) { $(link).parents('.dialogContent').html(data) }); } return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a>
				<?php $this->o($feature->title); ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div>
		<form method="post" action="<?php echo build_url('feature', 'linkfeature')?>" class="data-form ajaxForm">
		<?php if ($this->viaajax): ?>
		<input type="hidden" value="1" name="_ajax" />
		<?php endif; ?>
		<p>
		<label for="existing-features">Select Feature</label>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<input type="hidden" value="from" name="linktype" />
		<select id="existing-features" name="featureid">
		<option></option>
		<?php foreach ($this->projectFeatures as $feature): ?>
			<option value="<?php echo $feature->id?>"><?php $this->o($feature->title)?></option>
		<?php endforeach; ?>
		</select>
		<input type="submit" class="abutton" value="Link Feature" />
		</p>
		</form>
	</div>
	
	<div class="clear"></div>
	<!-- if an enhancement, allow the creation of a new feature from here -->
	<div>
		<h3>Children</h3>
		<ul>
			<?php foreach ($this->linkedToFeatures as $feature): ?>
			<li>
				<a title="Remove feature" href="#" onclick="if (confirm('Are you sure?')) { var link = this;  $.post('<?php echo build_url('feature', 'removeFeature', array('_ajax' => 1, 'id' => $this->model->id, 'featureid'=>$feature->id, 'linktype'=>'from'))?>', {}, function (data) { $(link).parents('.dialogContent').html(data) }); } return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a>
				<?php $this->o($feature->title); ?>

			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	
	<div>
		<form method="post" action="<?php echo build_url('feature', 'linkfeature')?>" class="data-form ajaxForm">
		<?php if ($this->viaajax): ?>
		<input type="hidden" value="1" name="_ajax" />
		<?php endif; ?>
		<p>
		<label for="existing-features-to">Select Feature</label>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<input type="hidden" value="to" name="linktype" />
		<select id="existing-features-to" name="featureid">
		<option></option>
		<?php foreach ($this->projectFeatures as $feature): ?>
			<option value="<?php echo $feature->id?>"><?php $this->o($feature->title)?></option>
		<?php endforeach; ?>
		</select>
		<input type="submit" class="abutton" value="Link Feature" />
		</p>
		</form>
	</div>
	

</fieldset>

<div id="notes">
</div>

<?php endif; ?>