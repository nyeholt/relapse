
<script type="text/javascript">
    $().ready(function() {
        $("#feature-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
</script>

<?php if ($this->model->id): ?>
<div id="parent-links">
    <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->model->projectid, '#features'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>
<?php endif; ?>

<h2>
<?php $this->o($this->model->id ? 'Edit "'.$this->model->title .'"': 'New Feature');?>
</h2>


<div id="feature-container">
	<ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->id): ?>
	        <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	        <li><a href="#features"><span>Other Features</span></a></li>
	        <li><a href="#notes"><span>Notes</span></a></li>
	        <li><a href="#tasks"><span>Tasks</span></a></li>
	        <?php endif; ?>
        <?php endif; ?>
    </ul>

    <div id="details">
	    
		<form method="post" action="<?php echo build_url('feature', 'save');?>">
		
		<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />

		<?php if (isset($this->parentfeature)): ?>
		<input type="hidden" value="<?php echo $this->parentfeature?>" name="parentfeature" />
		<?php endif;?>

		<?php if ($this->model->id): ?>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php endif; ?>
		
		<div class="inner-column">
			<?php $this->textInput('Feature Title', 'title'); ?>
		    <?php $this->textInput('Description', 'description', true); ?>
			<?php $this->textInput('Estimated effort (days)', 'estimated', false, ' size="4"'); ?>
		
		</div>
		<div class="inner-column">
			<?php $this->selectList('Priority', 'priority', $this->priorities) ?>
			<?php $this->textInput('Verification Steps', 'verification', true); ?>
		    
		</div>
		<p class="clear">
		    <input type="submit" class="abutton" value="Save" accesskey="s" />
		    <input type="button" class="abutton" onclick="history.go(-1);" value="Close" />
		</p>
		</form>
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
	
	    <div id="tasks">
	        <form method="post" action="<?php echo build_url('task', 'newtask')?>">
	            <input type="hidden" name="id" value="<?php echo $this->model->id?>" />
	            <input type="hidden" name="type" value="Feature" />
	            <p><label for="tasktitle">Title</label>
	            <input class="input" type="text" id="tasktitle" name="tasktitle" />
	            <input type="submit" value="Create Task" class="abutton" />
	            </p>
	        </form>
	            
	    	<div class="clear"></div>    
	        <h3>Tasks from this Feature</h3>
	        <table class="item-table" cellpadding="0" cellspacing="0">
		      <thead>
		      	<tr>
		      	<th>Title</th>
		      	<th width="40px"></th>
		      	</tr>
		      </thead>
		      <tbody>
		  	<?php foreach ($this->linkedTasks as $task): ?>
				<tr>
					<td><a href="<?php echo build_url('task', 'edit', array('id' => $task->id))?>"><?php $this->o($task->title);?></a></td>
					<td><a title="Remove link" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('task', 'removeLinkFrom', array('id' => $task->id, 'fromid' => $this->model->id, 'fromtype' => 'Feature', 'linktype'=>'to'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a></td>
				</tr>
		  	<?php endforeach; ?>
		  	</tbody>
		  	</table>
		  	
		  	
		  	<div>
				<h2>Link to task</h2>
				<div id="task-search-results">
					<table class="item-table">
					<thead>
						<tr>
						<th>Title</th>
						<th width="40px"></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->projectTasks as $task): ?>
						<tr>
							<td><?php $this->o($task->title);?></td>
							<td><a href="<?php echo build_url('task', 'linkfrom', array('fromtype'=>'Feature', 'fromid'=>$this->model->id, 'id'=>$task->id));?>">Link</a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
				</div>
			</div>
			
	    </div>
    <?php endif; ?>
</div>