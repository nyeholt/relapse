<script type="text/javascript">
    $().ready(function(){
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'milestone', fieldName: 'projectid'});
           
           });
        
        <?php if (!$this->project->id): ?>
        	$("select#clientid").change();
        <?php endif; ?>
        
        // add validation for the form
        $('.task-form').submit(function() {
        	// check the category isn't blank
        	if ($('#category').val() == '') {
        		alert("Category cannot be empty!");
        		$('#category').css('border', '1px solid red');
        		return false;
        	}

        	return true;
        });
    });
</script>

<?php if ($this->model->id): ?>
<div id="parent-links">
<?php if ($this->model->id): ?>
	<a title="Add Timsheet Record" href="#" 
	onclick="return displayTaskTimesheet(this, '<?php echo $this->model->id?>', '<?php echo build_url('timesheet','detailedTimesheet')?>');">
	<img class="small-icon" src="<?php echo resource('images/clock_red.png')?>" />
	</a>
	<a title="Start Timer" href="#" onclick="popup('<?php echo build_url('timesheet', 'record', array('id' => $this->model->id))?>', 'timer', '500', '300'); return false;"><img class="small-icon" src="<?php echo resource('images/clock_play.png')?>" />
    </a>
	<a title="Mark as complete" href="#" onclick="if (confirm('Really complete?')) { completeTask(<?php echo $this->model->id?>); } return false;"><img class="small-icon" src="<?php echo resource('images/accept.png')?>" /></a>
<?php endif; ?>

    <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#tasks'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>

<?php $this->hierarchy($this->model->getHierarchy()); ?>
<?php endif; ?>


<?php $statusClass = $this->model->id ? 'open-request' : 'new-request';  // only show a reduced set of info for non-new ?>

<div class="control-<?php echo $statusClass ?>" style="float: right">
	[<a href="#" onclick="$('.open-request').toggle();return false;">Expand Fields</a>]
</div>

<h2>
<?php $this->o($this->model->id ? 'Editing "'.$this->model->title.'"' : 'New Task');?>
</h2>

<?php if ($this->model->id): ?>
<div class="timesheet-div" style="display: none;" id="task-<?php echo $this->model->id?>-timesheet">
   	<span style="float: right;" >[<a href="#" onclick="$('.timesheet-div').hide(); return false;">X</a>]</span>
    <form id="task-<?php echo $this->model->id?>-timesheet-form2" action="<?php echo build_url('timesheet', 'addtime', array('taskid'=>$this->model->id))?>" method="post">
     From <input type="text" id="beginning-<?php echo $this->model->id?>" name="start" size="15" />, 
     add <input type="text" id="total-<?php echo $this->model->id?>" name="total" size="3" /> hours.
     <input type="submit" value="Add" class="abutton" />
     <?php $this->calendar('beginning-'.$this->model->id) ?>
     
     <script type="text/javascript">
         $(document).ready(
             function() {
                 var addTaskNoteForm = $('#task-<?php echo $this->model->id?>-timesheet-form2');
                 addTaskNoteForm.ajaxForm(function() { 
					$('#task-<?php echo $this->model->id?>-timesheet-detail').html("Loading...");
                 	$('#task-<?php echo $this->model->id?>-timesheet-detail').load('<?php echo build_url('timesheet','detailedTimesheet', array('taskid'=>$this->model->id))?>');
                 });
                 
             }
         );
     </script>
    </form>

    <div id="task-<?php echo $this->model->id?>-timesheet-detail">Loading...</div>
</div>
<?php endif; ?>

<div id="task-container">
	<ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
    </ul>
	
	<div id="details">
		<div class="bordered">
		<form method="post" action="<?php echo build_url('task', 'save');?>" class="task-form">
	    <?php $this->requestValidator() ?>

		<?php if ($this->model->id): ?>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php endif; ?>
		
		<div class="wide-form">
		<?php $this->textInput('Task Title', 'title') ?>
			<div class="<?php echo $statusClass ?>">
			<?php $this->textInput('Description', 'description', true); ?>
			</div>
		</div>
		<div class="inner-column">
			<?php $this->yesNoInput('Complete', 'complete', true); ?>
			<div class="<?php echo $statusClass ?>">
		    <?php $this->selectList('Assigned To', 'userid', $this->projectUsers, $this->u()->getUsername(), 'username', 'username', 5)?>
		    <?php $this->selectList('Category [<a href="#" onclick="$(\'#category-info\').toggle(); return false;">?</a>]', 'category', $this->categories, '', '', '', false, true) ?>
			    <div id="category-info" style="display: none">
	            	<ul>
	            		<li>Billable - Time recorded against this task is directly related to client work that must be billed</li>
	            		<li>Unbillable - Time that isn't to be billed to clients. Examples include
	            		</li>
	            		<li>Support - Time spent resolving any issues classified as a Bug or Support Request</li>
	            		<li>Free Support - Time spent resolving issues classified as a Bug or Support Request during the Free Guarantee Period</li>
	            		<li>Alfresco Support - Time spent resolving issues that are to do with the core Alfresco product, 
	            		including interacting with Alfresco support engineers</li>
	            		<li>Leave - Automatically set by the system for record time spent on leave</li>
	            	</ul>
	            </div>
			</div>
		</div>

		<div class="inner-column">
			
			<div class="<?php echo $statusClass ?>">
		    <p>
		    <label for="startdate">Start:</label>
		    <input readonly="readonly" type="text" class="input" name="startdate" id="startdate" value="<?php echo $this->model->startdate ? date('Y-m-d', strtotime($this->model->startdate)) : date('Y-m-d', time())?>" />
		    <?php $this->calendar('startdate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    </p>
		    <p>
		    <label for="due">Due:</label>
		    <input readonly="readonly" type="text" class="input" name="due" id="due" value="<?php echo $this->model->due ? date('Y-m-d', strtotime($this->model->due)) : date('Y-m-d', time() + 86400)?>" />
		    <?php $this->calendar('due', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    </p>
		    
		    <?php $this->textInput('Estimated hours', 'estimated', false, 'size="4"') ?>
		
		    <?php $this->selectList('Client', 'clientid', $this->clients, $this->project->clientid, 'id', 'title') ?>
		    <p>
		    <label for="project">Milestone:</label>
		    <?php $this->projectSelector('projectid', $this->projects, 'milestone', false, $this->project->id) ?>
		    </p>

		    <p>
		    	<label for="dependency">Depends On:</label>
				<select name="dependency" id="dependency">
					<option value=""></option>
				<?php foreach ($this->activeTasks as $activeTask) {
					// alright, if we're currently editing a task, and that
	                // task's dependency is a parent of the activeTask, then
	                // we can't select it (ie prevent infinite dependency loops!
	                if ($this->model->id == $activeTask->id) {
	                    continue;
	                }
	
	                if ($this->model->id && mb_strpos($activeTask->getDependencyId(), '-'.$this->model->id.'-') !== false) {
	                    // failure!
	                    continue;
	                }

	                $dependency = $activeTask->getDependencyId();
	                $selected = $dependency == $this->model->dependency;
				    ?>
				    <option value="<?php echo $dependency?>" <?php echo $selected ? "selected='selected'" : ''?>><?php $this->o($activeTask->title);?></option>
					<?php 
				}?>
				</select>
		    </p>
		    </div>
		    
		</div>
		
		<p style="clear: left;">
		<?php $this->autoComplete('Tags', 'tags', build_url('tag', 'suggest'), 'size="60"'); ?>
		<br/>
		    <input type="submit" class="abutton" value="Save" accesskey="s" />
		</p>
		</form>
		<!-- //end bordered box -->
		</div>
		
		<?php if ($this->model->id): ?>
			
			<div class="bordered">
				<h3>Requests</h3>
				<?php if (count($this->issues)): ?>
				<ul>
					<?php foreach ($this->issues as $issue): ?>
					<li>
					<?php
					$percentageComplete = 0;
			 		if ($issue->elapsed != 0 && $issue->estimated != 0) {
						$percentageComplete = ceil($issue->elapsed / $issue->estimated * 100);
			 		}
		        	?>
		        	<?php $this->percentageBar($percentageComplete)?>
		        	<?php 
		        		$unlinkUrl = build_url('task', 'removeLinkFrom', array('id' => $this->model->id, 'fromid' => $issue->id, 'fromtype' => 'Issue')); 
		        	?>
		        	<a href="#" onclick="if (confirm('Really remove link?')) location.href='<?php echo $unlinkUrl?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a>
		        	
		        	<a href="<?php echo build_url('issue', 'edit', array('id' => $issue->id))?>"><?php $this->o($issue->title)?></a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
				<form method="post" action="<?php echo build_url('task', 'linkFrom')?>" class="inlineform">
					<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
					<input type="hidden" name="fromtype" value="Issue" />
					
					<p>
						<select name="fromid">
						<?php foreach ($this->selectableRequests as $selectable): ?>
							<option value="<?php echo $selectable->id ?>"><?php $this->o($selectable->title)?></option>
						<?php endforeach; ?>
						</select>
						<input type="submit" value="Add" class="abutton" />
					</p>
				</form>
			</div>
			
			
			
			<div class="bordered">
				<h3>Features</h3>
				<?php if (count($this->features)): ?>
				<ul>
					<?php foreach ($this->features as $feature): ?>
					<li>
					<?php
					$percentageComplete = 0;
			 		if ($feature->estimated != 0 && $feature->hours != 0) {
						$percentageComplete = ceil($feature->hours / ($feature->estimated * za()->getConfig('day_length', 8)) * 100);
			 		}
		        	?>
		        	<?php $this->percentageBar($percentageComplete)?>
		        	
		        	<!-- unlink this feature -->
		        	<?php 
		        		$unlinkUrl = build_url('task', 'removeLinkFrom', array('id' => $this->model->id, 'fromid' => $feature->id, 'fromtype' => 'Feature')); 
		        	?>
		        	<a href="#" onclick="if (confirm('Really remove link?')) location.href='<?php echo $unlinkUrl?>'; return false;"><img src="<?php echo resource('images/link_break.png')?>" /></a>
		        	<a href="<?php echo build_url('feature', 'edit', array('id' => $feature->id))?>"><?php $this->o($feature->title)?></a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
				<form method="post" action="<?php echo build_url('task', 'linkFrom')?>" class="inlineform">
					<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
					<input type="hidden" name="fromtype" value="Feature" />
					
					<p>
						<select name="fromid">
						<?php foreach ($this->selectableFeatures as $selectable): ?>
							<option value="<?php echo $selectable->id ?>"><?php $this->o($selectable->title)?></option>
						<?php endforeach; ?>
						</select>
						<input type="submit" value="Add" class="abutton" />
					</p>
				</form>
			</div>
		
		<?php $this->noteList($this->notes, build_url('task', 'addnote'), $this->allUsers, $this->subscribers); ?>
		
		<!-- //end id'd stuff -->
		<?php endif; ?>
	
	</div>

</div>