
<script type="text/javascript">
    $().ready(function() {
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'project', fieldName: 'projectid'});
		});
        $("#issue-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
</script>

<?php $statusClass = $this->model->status == 'New' ? 'new-request' : 'open-request';  // only show a reduced set of info for non-new ?>

<form method="post" action="<?php echo build_url('issue', 'save');?>" class="data-form ajaxForm">
	<?php if ($this->viaajax): ?>
	<input type="hidden" value="1" name="_ajax" />
	<?php endif; ?>
	<fieldset>
		<legend>Details</legend>
		<?php if (isset($this->project)): ?>
		<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
		<?php endif; ?>
		<?php if (isset($this->client)): ?>
		<input type="hidden" value="<?php echo $this->client->id?>" name="clientid" />
		<?php endif;?>
		<?php if ($this->model->id): ?>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php else: ?>
		<?php endif; ?>
		<?php $this->textInput('Title', 'title', false, 'class="required"') ?>
		<p>Please provide as much information describing the impact of this request to help us better prioritise it</p>
		<?php $this->textInput('Description', 'description', true, 'class="required"') ?>
		<?php $this->textInput('Estimated effort (hours)', 'estimated', false, ' size="4"'); ?>
		<?php if ($this->model->id): ?>
		<p>
		<label>Created</label>
		<?php $this->o($this->u()->formatDate($this->model->created)); ?>
		</p>
		<p>
		<label>Created By</label>
		<?php $this->o($this->model->creator); ?>
		</p>
		<?php endif; ?>

		<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
			<?php if (isset($this->project)): ?>
				<?php $this->selectList('Assigned To', 'userid', $this->users, $this->project->manager, 'username', 'username', false, true)?>
			<?php else: ?>
				<?php $this->selectList('Assigned To', 'userid', $this->users, '', 'username', 'username', false, true)?>
			<?php endif; ?>
		<?php elseif (isset($this->project)): ?>
		<input type="hidden" name="userid" value="<?php $this->o($this->project->manager)?>" />
		<?php endif; ?>

		<?php $this->selectList('Severity [<a href="#" onclick="$(\'#severity-info\').toggle(); return false;">?</a>]', 'severity', $this->severities) ?>
		<div id="severity-info" style="display: none">
			<ul>
				<li>Severity 1 - A production system is not functioning at all and needs immediate attention</li>
				<li>Severity 2 - A system error is preventing normal operation from occurring</li>
				<li>Severity 3 - The request should be addressed when possible</li>
			</ul>
		</div>
		
		<?php // $this->valueList('Category', 'category', 'issue-form', $this->categories) ?>

		<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
		<?php $this->selectList('Status', 'status', $this->statuses) ?>
		<?php elseif($this->u()->username == $this->model->creator): ?>
		<?php $this->selectList('Status', 'status', $this->userStatuses) ?>
		<?php endif; ?>
	</fieldset>
	<fieldset>
		<legend>Additional Info</legend>
			<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
				<?php $this->yesNoInput('Private?', 'isprivate')?>
			<?php endif; ?>
			<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
			<?php $this->selectList('Type', 'issuetype', $this->types) ?>
			<?php endif; ?>
			<?php /*if ($this->u()->hasRole(User::ROLE_USER)): ?>
			<?php $this->valueList('To Be Fixed In', 'release', 'issue-form', $this->releases) ?>
			<?php else: ?>
				<p>
				<label for="release">To Be Fixed In:</label>
				<?php $this->o($this->model->release ? $this->model->release : "None" )?>
				</p>
			<?php endif;*/ ?>
			<?php $this->selectList('Product', 'product', $this->model->constraints['product']->getValues()) ?>
			<?php $this->selectList('Operating System', 'operatingsystem', $this->model->constraints['operatingsystem']->getValues()) ?>
			<div style="clear: both;"></div>
			<?php $this->selectList('Database', 'databasetype', $this->model->constraints['databasetype']->getValues()) ?>

			<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
				<p>
				<label for="clientid">Client:</label>
				<select name="clientid" id="clientid" class="required">
					<option></option>
					<?php
					$sel = $this->model->clientid ? $this->model->clientid : $this->client->id;
					foreach ($this->clients as $client): ?>
						<option value="<?php echo $client->id?>" <?php echo $sel == $client->id ? 'selected="selected"' : '';?>><?php $this->o($client->title);?></option>
					<?php endforeach; ?>
				</select>
				</p>

				<p>
				<label for="projectid">Project:</label>
				<?php $this->projectSelector('projectid', $this->projects != null ? $this->projects : array(), 'project', false, $this->project ? $this->project->id : null) ?>
				</p>
			<?php endif; ?>
	</fieldset>

	<p class="clear">
		<input type="submit" class="abutton" value="Save"  />
		<?php if ($this->viaajax): ?>
		<input type="button" class="abutton" onclick="$('#issuedialog').simpleDialog('close');" value="Close" />
		<?php endif ; ?>
	</p>

</form>

<?php if ($this->model->id): ?>
	<fieldset class="data-form">
		<legend>Notes</legend>
	<?php $this->noteList($this->notes, build_url('issue', 'addNote'), $this->allUsers, $this->subscribers); ?>
	</fieldset>
<?php endif; ?>

<?php if ($this->model->id && $this->u()->hasRole(User::ROLE_EXTERNAL)): ?>
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
			<?php $this->dialogPopin('taskdialog', $this->escape($openTask->title), build_url('task', 'edit', array('id'=>$openTask->id)), array('title' => 'Edit Task')); ?>
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
	<?php $this->dialogPopin('newLinkedTask', 'Add New Task', build_url('task', 'linkedtaskform', array('id' => $this->model->id, 'type'=> 'Issue')), array(), 'class="abutton"', 'input') ?>
	</p>
	<?php endif; ?>
</fieldset>


<fieldset id="files">
	<legend>Files</legend>
	<div>
		<ul id="file-listing">
			<?php foreach ($this->files as $file):?>
				<li>
				<?php if (is_string($file)): ?>
				<?php else: ?>
					<a class="action-icon" title="Edit file" href="<?php echo build_url('file', 'edit', array('id'=>$file->id, '__validation_token' => $this->requestValidator(true), 'projectid'=> $this->project->id, 'parent'=>base64_encode($file->path), 'returnurl'=>base64_encode('issue/edit/id/'.$this->model->id.'/projectid/'.$this->project->id.'/clientid/'.$this->client->id.'/#files')))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
					<a class="action-icon" title="Delete file" href="<?php echo build_url('file', 'delete', array('id'=>$file->id, '__validation_token' => $this->requestValidator(true), 'returnurl'=>base64_encode('issue/edit/id/'.$this->model->id.'/projectid/'.$this->project->id.'/clientid/'.$this->client->id.'/#files')))?>"><img src="<?php echo resource('images/delete.png')?>" /></a>
					<a href="<?php echo build_url('file', 'view', array('id' => $file->id, 'projectid' => $this->project->id)).htmlentities($file->filename)?>"><?php $this->o($file->getTitle())?></a>
					<?php if (!empty($file->description)): ?>
					<p>
						<?php $this->o($file->description) ?>
					</p>
					<?php endif; ?>
				<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<form method="post" action="<?php echo build_url('file', 'upload');?>" enctype="multipart/form-data">
		<input type="hidden" name="returnurl" value="<?php echo base64_encode('issue/edit/id/'.$this->model->id.'/projectid/'.$this->project->id.'/clientid/'.$this->client->id.'#files')?>" />
		<input type="hidden" value="<?php echo base64_encode($this->filePath)?>" name="parent" />
		<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />

		<div class="inner-column">
			<p>
			<label for="file">File</label>
			<input class="input" type="file" name="file" id="file" />
			</p>
			<p>
			<label for="filename">Filename:</label>
			<input class="input" type="text" name="filename" id="filename"/>
			</p>
			<p>
			<label for="title">Title:</label>
			<input class="input" type="text" name="title" id="title" />
			</p>

		</div>
		<div class="inner-column">
			<p>
			<label for="description">Description:</label>
			<textarea class="input" name="description"
				id="description"></textarea>
			</p>
		</div>
		<p class="clear">
			<input type="submit" class="abutton" value="Upload" accesskey="a" />
		</p>
	</form>
</fieldset>
	
    
<fieldset>
	<legend>History</legend>
	<table class="item-table" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<th width="25%">Title</th>
			<th>Status</th>
			<th>From</th>
			<th>Until</th>
			<th>Modified by</th>
		</tr>
		</thead>
		<tbody>
		<?php $index=0; foreach ($this->issueHistory as $oldIssue): ?>
		<tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
			<td><?php $this->o($oldIssue->title) ?></td>
			<td><?php $this->o($oldIssue->status) ?></td>
			<td style="text-align: center"><?php $this->o($oldIssue->validfrom) ?></td>
			<td style="text-align: center"><?php $this->o($oldIssue->created) ?></td>
			<td><?php $this->o($oldIssue->creator) ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>
    
<?php if (false): ?>

<div id="features">
	<h3>Features that link here</h3>
	<div class="inner-column">
		<form method="post" action="<?php echo build_url('issue', 'linkfeature')?>">
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
					<td style="text-align: right;"><a title="Remove feature" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('issue', 'removeFeature', array('id' => $this->model->id, 'featureid'=>$feature->id, 'linktype'=>'to'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
	</div>

	<?php if ($this->model->issuetype == 'Enhancement'): ?>
		<div class="clear"></div>
		<!-- if an enhancement, allow the creation of a new feature from here -->
		<h3>Features based on this issue</h3>

		<div class="inner-column">
			<form method="post" action="<?php echo build_url('issue', 'linkfeature')?>">
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
						<td style="text-align: right;"><a title="Remove feature" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('issue', 'removeFeature', array('id' => $this->model->id, 'featureid'=>$feature->id, 'linktype'=>'from'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			</table>
		</div>
	<?php endif; ?>

</div>
<?php endif; ?>

<!-- end model ID -->
<?php endif; ?>