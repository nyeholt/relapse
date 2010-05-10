<html>
<head>
<title>Timer</title>
<link type="text/css" rel="stylesheet" href="<?php echo theme_resource('relapse-style.css');?>" />

<?php $this->script(theme_resource('jquery-1.4.1.min.js')); ?>
<?php $this->script(resource('jq-plugins/formAjax.js')); ?>

<script type="text/javascript">
    $(document).ready(
        function () {
            window.focus();
		    setCompletion(<?php echo $this->task->getPercentage()?>);
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
		<span class="item-title"><?php $this->o($this->task->title) ?></span>
		<div id="task-time">
			<div><?php echo  $this->task->getDuration();?></div>
		</div>
		<p id="time-spent">
		</p>
		<p style="height: 70px; overflow: auto; text-align: left;">
			<?php $this->o($this->task->description); ?>
		</p>
	</div>
   	
	<br/>
	<input type="button" class="styled-input" onclick="this.value='Please wait...'; this.disabled = true; window.close();" value="Close" />
</div>
<script type="text/javascript">
updateTimeout = window.setTimeout('updateTime()', <?php echo $this->record->getUpdateTime()?> * 1000);
</script>
</body>
</html>