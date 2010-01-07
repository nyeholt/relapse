<html>
<head>
<title>Timer</title>
<link type="text/css" rel="stylesheet" href="<?php echo resource('style.css');?>" />
<?php $this->script(resource('jquery-1.2.2-b.js')); ?>
<?php $this->script(resource('jq-plugins/formAjax.js')); ?>

<script type="text/javascript">
var NOTES_URL = '<?php echo build_url('note', 'view')?>';

    $(document).ready(
        function () {
            window.focus();
		    setCompletion(<?php echo $this->task->getPercentage()?>);
		    
		    var addNoteForm = $('#add-note-form');
            addNoteForm.ajaxForm(function() { 
                $.get(NOTES_URL, {id: <?php echo $this->record->taskid?>, type: 'task'}, function(data) {
                   $('#task-notes-content').html(data);
                });
            });
            
            $.get(NOTES_URL, {id: <?php echo $this->record->taskid?>, type: 'task'}, function(data) {
                   $('#task-notes-content').html(data);
                });
        }
    );

    var updateTimeout;
	
	function updateTime()
	{
		// Get the current endtime
		var endtime = $('#endtime').val();
		var recordId = $('#record-id').val();
		var endpoint = '<?php echo  build_url('timesheet', 'update', array('taskid'=>$this->task->id));?>id/'+recordId+'/endtime/'+endtime;
		$.getScript(endpoint);
		updateTimeout = window.setTimeout('updateTime()', <?php echo $this->record->getUpdateTime()?> * 1000);
	}

	function refreshClose()
	{
	    if (window.opener != null) {
			window.opener.location.reload(true);
		}
		window.close();
	}
	
	function gotoTask()
	{
	    if (window.opener != null) {
			window.opener.location.href = '<?php echo build_url('task', 'edit', array('id'=>$this->task->id));?>';
		}
	}
	
	function setCompletion(percent)
	{
	    var color = "#7ED00A";
	    
	    if (percent > 0) {
    		var width = 200 * (percent / 100);
    		
    		if (width > 200) color = "#D10037";
    		
    		
	    }
		var bar = $('#time-spent');
		bar.css('color', color);
		
		bar.html(percent.toFixed(2)+'% Complete');
	}
	
	
</script>

</head>
<body id="timesheet">
<input type="hidden" id="record-id" value="<?php echo $this->record->id?>" /> 
<div>
<input type="hidden" id="endtime" value="<?php echo $this->record->starttime?>" />

	<div style="width: 70%; margin: .2em auto; text-align: center;">
	<div>
	<span class="item-title"><?php $this->o($this->task->title) ?></span> &raquo; <span id="task-time"><?php echo  $this->task->getDuration();?></span>
	</div>
	<p id="time-spent">
	</p>
	</div>
   	<p style="height: 70px; overflow: auto; border: 1px solid #ededed">
   	    <?php $this->o($this->task->description); ?>
   	</p>
   	
<br/>

	<input type="button" class="styled-input" onclick="this.value='Please wait...'; this.disabled = true; window.close();" value="Close" />
	<input type="button" class="styled-input" onclick="gotoTask();" value="Goto" />
	<br/><br/>
	<h4>Related FAQs</h4>
	<ol>
		<?php foreach ($this->relatedFaqs as $faq): ?>
			<li>
				<a href="#" onclick="window.opener.location='<?php echo build_url('faq', 'view', array('id'=>$faq->id))?>'"><?php $this->o($faq->title); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</div>
<script type="text/javascript">
updateTimeout = window.setTimeout('updateTime()', <?php echo $this->record->getUpdateTime()?> * 1000);
</script>
</body>
</html>