
<div>
    <h2>Subscribed Items</h2>
    <table class="item-table" cellpadding="0" cellspacing="0">
	    <thead>
	    <tr>
	    	<th width="10%">Type</th>
	    	<th width="5%">Id</th>
	        <th>Path</th>
	    	<th width="50%">Name</th>
	    	<th width="5%"></th>
	    </tr>
	    </thead>
	    <tbody>
    <?php 
    foreach ($this->items as $item) {
    	$itemType = mb_strtolower(get_class($item));
    	// if ($itemType == 'project' || $itemType == 'issue' || $itemType=='task') {
    		$rowId = 'item-'.$itemType.'-'.$item->id;
    		$hierarchy = array();
    		if (method_exists($item, 'getHierarchy')) {
    			$hierarchy = $item->getHierarchy();
    		}
        ?>
        <tr id="<?php $this->o($rowId)?>">
        	<td><?php $this->o(get_class($item));?></td>
        	<td align="center"><?php $this->o($item->id); ?></td>
        	<td><?php $this->hierarchy($hierarchy, '&raquo;', null); ?>
        	<td width="50%"><a href="<?php echo build_url($itemType, 'view', array('id'=>$item->id));?>"><?php $this->o($item->title);?></a></td>
			<td><a title="Unsubscribe" id="delete-project-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $item->id?>', type:'<?php echo get_class($item)?>'}, function() {$('#<?php echo $rowId?>').remove();}); return false; "><img src="<?php echo resource('images/thumb_down.png')?>"/></a></td>
	    </tr>
        <?php 
		// }
    }
    ?>
    	</tbody>
    </table>
</div>

<div style="clear: left;"></div>
<div id="todays-times">
	<h2>Today's Times</h2>
	<div id="todays-timetable" class="timetable"></div>

	<input type="hidden" name="timestart" id="timestart" />
	<input type="hidden" name="timeend" id="timeend" />


	<script type="text/javascript">
	var ttable = new TimeTable('todays-timetable', 'timestart', 'timeend', 1, false);
	ttable.create(5, 23, .25, 1);
	
	<?php 
	
	$taskColors = array(
		'#ee681e',
		'#ee1e32',
		'#4b1eee',
		'#1ed2ee',
		'#1eee1f',
		'#dcee1e',
		'red',
		'blue',
		'green',
		'grey'
	);
	
	function normaliseTaskTime($time) 
	{
		// convert to h:m:s, then round it up to the nearest quarter of an hour
		$time = date('H:i', $time);
		// split by the :, we want to convert that to a decimal
		$bits = split(":", $time);
		$mins = $bits[1];
		// convert to nearest 1/4 of an hour
		$percentage = ((int) ((100 * ($mins / 60) + 24) / 25)) *  25;
		if ($percentage == 0) {
			return $bits[0];
		}

		return $bits[0].'.'.$percentage;
	}
	?>
	
	
	
	// load sessions from php
	<?php foreach ($this->dayTasks as $timerecord): 
		$index = $timerecord->taskid % 10;
		$color = $taskColors[$index];
	?>
	ttable.lockSession('<?php echo normaliseTaskTime($timerecord->starttime)?>','<?php echo normaliseTaskTime($timerecord->endtime)?>', '<?php $this->o($color)?>', '<?php $this->o(str_replace("'", "\\'", $timerecord->getTaskTitle()).' - '.date('Y-m-d H:i:s', $timerecord->starttime).' to '.date('Y-m-d H:i:s', $timerecord->endtime))?>');
	
	<?php endforeach; ?>
	ttable.redraw();
	// ttable.setSessions({start:6.25,end:11,color:"#f00",tooltip:"stuff"}, {start:14.50,end:16});
	</script>
</div>

<h2>Current Month's Times (<a href="<?php echo build_url('timesheet', 'index', array('username' => $this->u()->username, 'start'=>$this->startDate, 'end'=>$this->endDate))?>">View Details</a>)</h2>
<div id="current-month-times">
	<table class="item-table" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
	    <?php foreach ($this->categories as $cat):?> 
	    <th><?php $this->o($cat)?></th>
	    <?php endforeach; ?>
	    <th>Total</th>
	</tr>
	</thead>
	<tbody>
	<?php
	
	$grandTotal = 0;
	$index=0; 
	
	$inCategory = array();
	foreach ($this->taskInfo as $info) {
		$total = 0;
		foreach ($info->days as $dayTime) {
			$total += $dayTime;	
		}
		
		$current = ifset($inCategory, $info->taskcategory, 0);
		$inCategory[$info->taskcategory] = $current + $total;
	}
	?>
	<tr>
		<?php 
		$userTotal = 0;
		foreach ($this->categories as $cat): 
			$dayTime = ifset($inCategory, $cat);
			$userTotal += $dayTime;
		?>
		<td align="center">
			<?php $this->o($this->elapsedTime($dayTime)); ?>
		</td>
		<?php endforeach; ?>
		<td align="center">
			<?php $this->o($this->elapsedTime($userTotal));?>
		</td>
	</tr>
	</tbody>
	</table>
</div>
<ul class="fat-listing" id="common-tasks">
	<li><a href="<?php echo build_url('tasks', 'list')?>">Tasks</a></li>
	<li><a href="<?php echo build_url('user', 'edit', array('#leave'))?>">Leave</a></li>
	<li><a href="<?php echo build_url('user', 'edit', array('#expenses'))?>">Expenses</a></li>
</ul>
