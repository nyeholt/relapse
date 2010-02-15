
<div id="feature-container">
	
    <div id="details">
	    
		<form method="post" action="<?php echo build_url('feature', 'save');?>">
		
		<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />

		<?php if (isset($this->parentfeature)): ?>
		<input type="hidden" value="<?php echo $this->parentfeature?>" name="parentfeature" />
		<?php endif;?>

		<?php if ($this->model->id): ?>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php endif; ?>
		
		<div class="wide-form">
		<?php $this->textInput('Feature Title', 'title') ?>
		<?php $this->textInput('Description', 'description', true); ?>
		<?php $this->textInput('Implementation Steps', 'implementation', true); ?>
		<?php $this->textInput('Verification Steps', 'verification', true); ?>
		</div>
		
		<div class="inner-column">
			<?php $this->yesNoInput('Complete', 'complete', true); ?>
			<?php $this->textInput('Estimated effort (days)', 'estimated', false, ' size="4"'); ?>
		</div>
		<div class="inner-column">
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
		</div>
		<p class="clear">
		    <input type="submit" class="abutton" value="Save" accesskey="s" />
		    <input type="button" class="abutton" onclick="history.go(-1);" value="Close" />
		</p>
		</form>
		
		<?php if ($this->model->id && $this->u()->hasRole(User::ROLE_USER)): ?>
		<div id="tasks" class="bordered">
        	<h3>Tasks</h3>
        	<ul class="project-task-summary">
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
	 			<a href="<?php echo build_url('task', 'edit', array('id'=>$openTask->id))?>"><?php $this->o($openTask->title)?></a>
	 			<?php else: ?>
	 			<?php $this->o($openTask->title)?>
	 			<?php endif; ?>
	 			<?php $estimated += $openTask->estimated; $taken += $openTask->timespent; ?>
	 			</li>
	 		<?php endforeach; ?> 
	 		</ul>

	 		<p>Time taken: <?php $this->o(sprintf("%.2f", $taken > 0 ? $taken / 3600 : 0)) ?> / <?php $this->o($estimated) ?> hours</p>

			<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>    
        	<form method="post" action="<?php echo build_url('task', 'newtask')?>">
	            <input type="hidden" name="id" value="<?php echo $this->model->id?>" />
	            <input type="hidden" name="type" value="Feature" />
	            <p><label for="tasktitle">Add New Task</label>
	            <input class="input" type="text" id="tasktitle" name="tasktitle" />
	            In Milestone
	            <?php $this->projectSelector('newtaskProjectid', $this->projects, 'milestone', false, $this->model->milestone) ?>
	            <input type="submit" value="Create Task" class="abutton" />
	            </p>
	        </form>
	        
	        <?php endif; ?>
        </div>
        <?php endif; ?>
	</div>
	<?php if ($this->model->id && $this->u()->hasRole(User::ROLE_USER)): ?>
	<div id="features">
    	<h3>Parent features</h3>
	    <div class="inner-column">
			<form method="post" action="<?php echo build_url('feature', 'linkfeature')?>">
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
		<div class="inner-column">
			<table class="item-table">
			<thead>
				<tr>
				<th width="90%">Title</th>
				<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->linkedFromFeatures as $feature): ?>
					<tr>
						<td><a href="<?php echo build_url('feature', 'edit', array('id'=>$feature->id))?>"><?php $this->o($feature->title); ?></a></td>
						<td style="text-align: right;"><a title="Remove feature" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('feature', 'removeFeature', array('id' => $this->model->id, 'featureid'=>$feature->id, 'linktype'=>'to'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			</table>
		</div>
		
		<div class="clear"></div>
		<!-- if an enhancement, allow the creation of a new feature from here -->
		<h3>Child Features</h3>
		
		<div class="inner-column">
			<form method="post" action="<?php echo build_url('feature', 'linkfeature')?>">
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
		<div class="inner-column">
			<table class="item-table">
			<thead>
				<tr>
				<th width="90%">Title</th>
				<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->linkedToFeatures as $feature): ?>
					<tr>
						<td><a href="<?php echo build_url('feature', 'edit', array('id'=>$feature->id))?>"><?php $this->o($feature->title); ?></a></td>
						<td style="text-align: right;"><a title="Remove feature" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('feature', 'removeFeature', array('id' => $this->model->id, 'featureid'=>$feature->id, 'linktype'=>'from'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			</table>
		</div>
		
    </div>
		
	    <div id="notes">
		</div>
	
    <?php endif; ?>
</div>