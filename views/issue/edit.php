
<script type="text/javascript">
    $().ready(function() {
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'any', fieldName: 'projectid'});
           
           });
        
        $("#issue-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
</script>

<?php if ($this->model->id): ?>
<div id="parent-links">
    <?php if (isset($this->project)): ?>
        <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#issues'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
    <?php endif;?>
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->client->id, '#issues'));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
</div>
<?php endif; ?>

<h2>
<?php $this->o($this->model->id ? 'Edit "'.$this->model->title.'"' : 'New Request');?>
</h2>

<div id="issue-container">
    <ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->id): ?>
        <li><a href="#files"><span>Files</span></a></li>
        <li><a href="#notes"><span id="notes-heading">Notes (<?php echo count($this->notes)?>)</span></a></li>
        <li><a href="#history"><span>History</span></a></li>
	        <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	        <li><a href="#features"><span>Features</span></a></li>
	        <li><a href="#tasks"><span>Tasks</span></a></li>
	        <?php endif; ?>
        <?php endif; ?>
    </ul>
    <div id="details">
        <form method="post" action="<?php echo build_url('issue', 'save');?>" class="issue-form">
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
        
        <div class="inner-column">
        	<?php $this->textInput('Request Title', 'title') ?>
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
		        	<?php $this->selectList('Assigned To', 'userid', $this->users, $this->u()->getUsername(), 'username', 'username', false, true)?>		        
		        <?php endif; ?>
        	<?php elseif (isset($this->project)): ?>
        	<input type="hidden" name="userid" value="<?php $this->o($this->project->manager)?>"></input>
        	<?php endif; ?>

            <?php $this->selectList('Severity', 'severity', $this->severities) ?>
        	<?php $this->selectList('Status', 'status', $this->statuses) ?>
            <?php $this->valueList('Category', 'category', 'issue-form', $this->categories) ?>
	        
        </div>
        <div class="inner-column">
        	
            <?php $this->selectList('Type', 'issuetype', $this->types) ?>
            <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
            <?php $this->valueList('To Be Fixed In', 'release', 'issue-form', $this->releases) ?>
            <?php else: ?>
            	<p>
	            <label for="release">To Be Fixed In:</label>
	            <?php $this->o($this->model->release ? $this->model->release : "None" )?>
	            </p>
            <?php endif; ?>
            <?php $this->selectList('Product', 'product', $this->model->constraints['product']->getValues()) ?>
            <?php $this->selectList('O/S', 'operatingsystem', $this->model->constraints['operatingsystem']->getValues()) ?>
            <?php $this->selectList('Database', 'databasetype', $this->model->constraints['databasetype']->getValues()) ?>
            
        	<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>    
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
			    <label for="project">Project:</label>
			    <?php $this->projectSelector('projectid', $this->projects) ?>
			    </p>
	        <?php endif; ?>    
        </div>
        <div class="wide-area" style="clear: left;">
        	<?php $this->textInput('Description', 'description', true) ?>
        </div>
        <p class="clear">
            <input type="submit" class="abutton" value="Save" accesskey="s" />
            <?php if (isset($this->project)): ?>
            <input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'view', array('id'=>$this->project->id, "#issues"))?>'" value="Close" />            
            <?php endif; ?>

        </p>
        </form>
    </div>
    
    <?php if ($this->model->id): ?>
    <div id="files">
	    <div>
	    	<ul id="file-listing">
				<?php foreach ($this->files as $file):?>
				    <li>
				    <?php if (is_string($file)): ?>
				    <?php else: ?>
				        <a class="action-icon" title="Edit file" href="<?php echo build_url('file', 'edit', array('id'=>$file->id, 'projectid'=> $this->project->id, 'parent'=>base64_encode($file->path), 'returnurl'=>base64_encode('issue/edit/id/'.$this->model->id.'/projectid/'.$this->project->id.'/clientid/'.$this->client->id.'/#files')))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
				        <a class="action-icon" title="Delete file" href="<?php echo build_url('file', 'delete', array('id'=>$file->id, 'returnurl'=>base64_encode('issue/edit/id/'.$this->model->id.'/projectid/'.$this->project->id.'/clientid/'.$this->client->id.'/#files')))?>"><img src="<?php echo resource('images/delete.png')?>" /></a>
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
	   	
	   	<p>
	   		<a class="abutton" href="#" onclick="$('#file-adding').show(); return false;">Add File</a>
	   	</p>
	    <div id="file-adding" style="display: none">
	    
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
	    </div>
	</div>
	
    <div id="notes">
        
        <?php $note = null;?>
        <?php foreach ($this->notes as $note): ?>
        
        <div class="note">
            <div class="note-header">
                <h4><?php $this->o($note->title)?></h4>
                <span class="note-by">By <?php $this->o($note->userid.' on '.$note->created)?></span>
            </div>
            <div class="note-body"><?php $this->bbCode($note->note);?></div>
        </div>
        
        <?php endforeach; ?>
        
        <?php $deleteStyle = isset($this->existingWatch) ? 'inline' : 'none' ?>
        <?php $addStyle = $deleteStyle == 'inline' ? 'none' : 'inline' ?>
        
        <div class="note">
	        
	        <form id="issue-add-note-form" method="post" action="<?php echo build_url('issue', 'addNote');?>">
	            <input type="hidden" value="<?php echo $this->model->id?>" name="id"/>
	            <p>
	            <label for="note-title">Title:</label>
	            <input class="input" type="text" name="title" id="note-title" value="Re: <?php $this->o($note ? $note->title : $this->model->title)?>" />
	            </p>
	            <p>
	            <label for="note-note">Note:</label>
	            <div class="wide-area">
	            <textarea name="note" rows="8" cols="80" id="note-note"></textarea>
	            </div>
	            </p>
	            <p class="clear">
	                <input type="submit" class="abutton" value="Add Note" accesskey="a" />
	                <a class="abutton" style="display: <?php echo $deleteStyle?>;" id="delete-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->model->id?>', type:'<?php echo get_class($this->model)?>'}, function() {$('#delete-watch').hide();$('#add-watch').show(); }); return false; ">Remove Watch</a>
		        	<a class="abutton" style="display: <?php echo $addStyle?>;" id="add-watch" href="#" onclick="$.get('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->model->id?>', type:'<?php echo get_class($this->model)?>'}, function() {$('#add-watch').hide();$('#delete-watch').show(); }); return false; ">Add Watch</a>
	            </p>
	            
	        </form>
        </div>
    </div>
    
    <div id="history">
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
		        <td style="text-align: center"><?php $this->o($oldIssue->updated) ?></td>
		        <td style="text-align: center"><?php $this->o($oldIssue->lastchanged) ?></td>
		        <td><?php $this->o($oldIssue->modifiedby) ?></td>
		    </tr>
		    <?php endforeach; ?>
		    </tbody>
		</table>
    </div>
    
    <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
    
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
    <div id="tasks">
    	<div class="inner-column">
	        <form method="post" action="<?php echo build_url('task', 'newtask')?>">
	            <input type="hidden" name="id" value="<?php echo $this->model->id?>" />
	            <input type="hidden" name="type" value="Issue" />
	            <p><label for="tasktitle">Title</label>
	            <input class="input" type="text" id="tasktitle" name="tasktitle" />
	            <input type="submit" value="Create Task" class="abutton" />
	            </p>
	        </form>
	    </div>
	    <div class="inner-column">
	        
	
		</div>    
	    <div class="clear"></div>
	        <h3>Tasks from this Issue</h3>
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
					<td><a title="Remove link" href="#" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('task', 'removeLinkFrom', array('id' => $task->id, 'fromid' => $this->model->id, 'fromtype' => 'Issue', 'linktype'=>'to'))?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a></td>
				</tr>
		  	<?php endforeach; ?>
		  	</tbody>
		  	</table>
	    
	    <div class="clear">
			<p>Link to task</p>
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
						<td><a href="<?php echo build_url('task', 'linkfrom', array('fromtype'=>'Issue', 'fromid'=>$this->model->id, 'id'=>$task->id));?>">Link</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
				
			</div>
		</div>
	</div>
	    
        <?php endif; ?>
    <?php endif; ?>
<!--/issue-container-->
</div>