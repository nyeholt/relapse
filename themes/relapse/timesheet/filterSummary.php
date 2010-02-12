<script type="text/javascript">
    $().ready(function(){
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'project', fieldName: 'projectid', empty: '1', showMilestones:'0'});
           
           });
        
        	$("select#clientid").change();

    });
</script>

<div class="std">
<h2>
Select filter criteria for timesheet summary report
</h2>

	<form method="post" action="<?php echo build_url('timesheet', 'summaryReport');?>" class="data-form">
    <?php $this->requestValidator() ?>

	<div class="wide-form">
		<div class="inner-column">
		    <?php $this->selectList('Format', 'outputformat', array("csv", "html"), "html")?>

		    <?php $this->selectList('User', 'username', $this->allUsers, '', 'username', 'username', false, true)?>
		    <?php $this->selectList('Client', 'clientid', $this->clients, 0, 'id', 'title', false, true) ?>
		    <p>
		    <label for="projectid">Project:</label>
		    <?php $this->projectSelector('projectid', $this->projects, 'project', true) ?>
		    </p>

		</div>


		<div class="inner-column">
			
		    <p>
		    <label for="start">Start:</label>
		    <input readonly="readonly" type="text" class="input" name="start" id="start" value="" />
		    <?php $this->calendar('start', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    </p>
		    <p>
		    <label for="end">End:</label>
		    <input readonly="readonly" type="text" class="input" name="end" id="end" value="" />
		    <?php $this->calendar('end', 'ifFormat:"%Y-%m-%d", showsTime:false'); ?>
		    </p>
			<p>
			<b>Full Day:</b> <?php echo(za()->getConfig('day_length', 8));?> hours
			</p><p>
			<b>Half Day:</b> <?php echo(za()->getConfig('day_length', 8)/2);?> hours
			</p>		    
			<br/>
		    <input type="submit" class="abutton" value="Send" accesskey="s" />
		</div>
	</div>
	</form>
</div>
		
