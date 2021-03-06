<script type="text/javascript">
    $().ready(function(){
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'milestone', fieldName: 'projectid'});
           
           });
        
        <?php if (!$this->project->id): ?>
        	$("select#clientid").change();
        <?php endif; ?>
    });
</script>

<div id="task-container">
	<form method="post" action="<?php echo build_url('task', 'save');?>" class="data-form ajaxForm">

    <?php $this->requestValidator() ?>
	<?php if ($this->viaajax): ?>
		<input type="hidden" name="_ajax" value="1" />
	<?php endif; ?>
	
	<?php if ($this->model->id): ?>
	<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
	<?php endif; ?>
	<fieldset class="primaryDetails">
		<legend>Task Info</legend>
		<?php $this->textInput('Task Title', 'title') ?>
		<?php $this->textInput('Description', 'description', true); ?>
		<?php $this->valueList('Category', 'category', 'task-form', $this->categories) ?>
	    <?php $this->selectList('Assigned To', 'userid', $this->projectUsers, $this->u()->getUsername(), 'username', 'username', 5)?>
	</fieldset>
	<fieldset class="additionalDetails">
		<legend>More details</legend>
		<?php $this->yesNoInput('Complete', 'complete', true); ?>
	    <p>
	    <label for="startdate">Start:</label>
	    <input readonly="readonly" type="text" class="input calendarInput" name="startdate" id="startdate" value="<?php echo $this->model->startdate ? date('Y-m-d', strtotime($this->model->startdate)) : date('Y-m-d', time())?>" />
	    <?php $this->calendar('startdate', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
	    </p>
	    <p>
	    <label for="due">Due:</label>
	    <input readonly="readonly" type="text" class="input calendarInput" name="due" id="due" value="<?php echo $this->model->due ? date('Y-m-d', strtotime($this->model->due)) : date('Y-m-d', time() + 86400)?>" />
	    <?php $this->calendar('due', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
	    </p>

	    <?php $this->textInput('Estimated hours', 'estimated', false, 'size="4"') ?>

	    <?php $this->selectList('Client', 'clientid', $this->clients, $this->project->clientid, 'id', 'title') ?>
	    <p>
	    <label for="project">Project:</label>
	    <?php $this->projectSelector('projectid', $this->projects, 'milestone') ?>
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
	    
	</fieldset>

	<fieldset>
	<p class="clear-left">
	<?php $this->autoComplete('Tags', 'tags', build_url('tag', 'suggest'), 'size="30"'); ?>
	</p>
	</fieldset>

	<input type="submit" class="abutton" value="Save" accesskey="s" />
	<?php if ($this->viaajax): ?>
	<input type="button" class="abutton" onclick="$('#taskdialog').simpleDialog('close');" value="Close" />
	<?php else: ?>
	<input type="button" class="abutton" onclick="location.href='<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#tasks'))?>'" value="Close" />
	<?php endif; ?>
	
	</form>

	<?php if ($this->model->id): ?>
	<fieldset>
		<legend>Notes</legend>
		<?php $deleteStyle = isset($this->existingWatch) ? 'inline' : 'none' ?>
        <?php $addStyle = $deleteStyle == 'inline' ? 'none' : 'inline' ?>
        
        <div id="watch-controls">
        </div>
        
        <?php $note = null; ?>
        <?php foreach ($this->notes as $note): ?>
        
        <div class="note">
            <div class="note-header">
                <h4><?php $this->o($note->title)?></h4>
                <span class="note-by">By <?php $this->o($note->userid.' on '.$note->created)?></span>
            </div>
            <div class="note-body"><?php $this->bbCode($note->note);?></div>
        </div>
        
        <?php endforeach; ?>
        <h3>Add new note</h3>
        <form method="post" action="<?php echo build_url('note', 'add');?>">
            <input type="hidden" value="<?php echo get_class($this->model)?>" name="attachedtotype"/>
            <input type="hidden" value="<?php echo $this->model->id?>" name="attachedtoid"/>
            <input type="hidden" value="<?php echo za()->getUser()->getUsername()?>" name="userid"/>
            <p>
            <label for="note-title">Title:</label>
            <input class="input" type="text" name="title" id="note-title" value="Re: <?php $this->o($note ? $note->title : $this->model->title)?>" />
            </p>
            <p>
            <label for="note-note">Note:</label>
            <textarea name="note" rows="5" cols="45" id="note-note"></textarea>
            </p>
            <p>
                <input type="submit" class="abutton" value="Add Note" accesskey="a" />
                <a class="abutton" style="display: <?php echo $deleteStyle?>;" id="delete-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->model->id?>', type:'<?php echo get_class($this->model)?>'}, function() {$('#delete-watch').hide();$('#add-watch').show(); }); return false;">Remove Watch</a>
        		<a class="abutton" style="display: <?php echo $addStyle?>;" id="add-watch" href="#" onclick="$.get('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->model->id?>', type:'<?php echo get_class($this->model)?>'}, function() {$('#add-watch').hide();$('#delete-watch').show(); }); return false;">Add Watch</a>
            </p>
        </form>
	</fieldset>
	<?php endif; ?>
</div>