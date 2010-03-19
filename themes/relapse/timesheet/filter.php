<script type="text/javascript">
    $().ready(function(){
        $("select#clientid").change(function(){
        	$('#projectSelector-projectid').load('<?php echo build_url('project', 'projectSelector')?>', {clientid: $(this).val(), selectorType: 'project', fieldName: 'projectid', empty: '1', showMilestones:'0'});
           
           });
        
        	$("select#clientid").change();

    });
</script>


<h2>
Select filter criteria for detailed timesheet report
</h2>

	<form method="post" action="<?php echo build_url('timesheet', 'index');?>" class="task-form">
    <?php $this->requestValidator() ?>

	<div class="wide-form">
		<div class="inner-column">
		    <?php $this->selectList('Format', 'outputformat', array("csv", "html"), "csv")?>

		    <?php $this->selectList('User', 'username', $this->allUsers, '', 'username', 'username', false, true)?>
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
		    <input type="submit" class="abutton" value="Send"  />
		</div>
	</div>
	</form>

		
