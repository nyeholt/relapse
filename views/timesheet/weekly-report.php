<?php 
unset($this->params['controller']);
unset($this->params['action']);
unset($this->params['module']);
?>

<?php if (isset($this->params['projectid'])): ?>
<div id="parent-links">
    <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->params['projectid'], '#timesheet'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>
<?php endif; ?>
<?php if (isset($this->params['clientid'])): ?>
<div id="parent-links">
    <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->params['clientid'], '#timesheet'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>
<?php endif; ?>

<h3>
<?php $this->o($this->title); ?> Report
</h3>
<ul>
	<li>From: <?php $this->o($this->startDate)?></li>
	<li>To: <?php $this->o($this->endDate); ?></li>
<?php if ($this->user): ?>
	<li>User: <?php $this->o($this->user->username);?></li>
<?php endif; ?> 
<?php if ($this->client): ?>
	<li>Client: <?php $this->o($this->client->title);?></li>
<?php endif; ?> 
<?php if ($this->project): ?>
	<li>Project: <?php $this->o($this->project->title);?> (<?php $this->o($this->project->id);?>)</li>
<?php endif; ?> 
<?php if ($this->category): ?>
	<li>Category: <?php $this->o($this->category);?></li>
<?php endif; ?> 
</ul>
<?php if (isset($this->params)): ?>
<p>
<a href="<?php echo build_url('timesheet', 'export', $this->params)?>" class="abutton">Export To CSV</a>
</p>
<?php endif; ?>
<?php 
$days = array();
$curr = $start = strtotime($this->startDate);
$end = strtotime($this->endDate);

while ($curr < $end) {
    $days[] = date('D d/m', $curr);
    $curr += 86400;
}
?>
<br/><br/>
<table class="item-table" cellpadding="0" cellspacing="0">
<thead>
<tr>
    <th width="10%">User</th>
    <th>Project ID</th>
    <!-- stuff for hierarchy output -->
    <?php for ($i = 0; $i < $this->maxHierarchyLength; $i++): 
    	$output = "Project";
    	if ($i == $this->maxHierarchyLength - 1) {
    		$output = "Milestone";
    	} else if ($i == 0) {
    		$output = "Client";
    	}
    	?>
    	<th><?php echo $this->o($output) ?></th>
    <?php endfor; ?>
    <th>Task</th><th>Task ID</th>    <th>Category</th>
    <?php foreach ($days as $day): ?>
        <th><?php $this->o($day);?></th>
    <?php endforeach;?>
    <th>Total</th>
</tr>
</thead>
<tbody>
<?php

$dayTotals = array();
$grandTotal = 0;
$index=0; 

foreach ($this->taskInfo as $info) {
	$parentProjects = array();
	$milestone = null;
	$hierarchy = $this->hierarchies[$info->taskid];    
   	
	// figure out which is the project that needs to be output as the main ID
	$immediateParent = null;
	
	// we know the client is hierarchy at 0, so projects is everything between
	for ($i = 1, $c = count($hierarchy); $i < $c; $i ++) {
		$hierarchyItem = $hierarchy[$i];
		if ($hierarchyItem->ismilestone) {
			$milestone = $hierarchyItem; 
		} else {
			$parentProjects[] = $hierarchyItem;
			$immediateParent = $hierarchyItem;
		}
	}

    /* @var $info TaskInfo */
    ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
    <td>
        <a href="<?php echo build_url('timesheet', 'index', array('username'=>$info->user))?>"><?php $this->o($info->user)?></a>
    </td>
    <td><?php $this->o($immediateParent != null ? $immediateParent->id : ''); ?></td>
    
    <td><a href="<?php echo build_url('client', 'view', array('id'=>$info->clientid))?>"><?php $this->o($info->clienttitle)?></a></td>
    <!-- stuff for hierarchy output -->
    <?php 
    for ($i = 0; $i < $this->maxHierarchyLength - 2; $i++):
    // we always need to output enough <td></td> to make sure that the table alignment matches properly
	// which is why we go to the max length of the hierarchy, not just as many parent projects there are
	// we cut 2 off the length because we're ignoring the milestone and the client that are also in the hierarchy
    ?>
    <td>
    	<?php if (isset($parentProjects[$i])): 
    	$project = $parentProjects[$i];
    	?>
    	<a href="<?php echo build_url('project', 'view', array('id'=>$project->id))?>"><?php $this->o($project->title)?></a>
    	<?php endif; ?>
    </td>
    <?php endfor; ?>
    
    <!-- the milestone, if any -->
    <td><?php if ($milestone != null): ?><a href="<?php echo build_url('project', 'view', array('id'=>$milestone->id))?>"><?php $this->o($milestone->title)?></a><?php endif; ?></td>
    
    <td><a href="<?php echo build_url('task', 'edit', array('id'=>$info->taskid))?>"><?php $this->o($info->title)?></a></td>
    <td><?php $this->o($info->taskid)?></td>
    <td><?php $this->o($info->taskcategory)?></td>
    
    <!-- 
    
    <td><a href="<?php echo build_url('project', 'view', array('id'=>$info->projectid))?>"><?php $this->o($info->projecttitle)?></a></td>
    
     -->

    <?php $taskTotal = 0; ?>
    <?php foreach ($days as $day): ?>
        <?php 
        $taskTotal += ifset($info->days, $day, 0); 
        $dayTotal = ifset($dayTotals, $day, 0);
        $dayTotals[$day] = $dayTotal + ifset($info->days, $day, 0);
        ?>
        <td><?php echo $this->elapsedTime(ifset($info->days, $day, 0));?></td>
    <?php endforeach;?>
    <td><?php echo $this->elapsedTime($taskTotal); ?></td>
    </tr>
    <?php
}
?>
<tr class="total-row">
    <td>Totals</td>
    <td></td>
    <!-- stuff for hierarchy output -->
    <td></td>
        <td></td><td></td>
    <?php for ($i = 0; $i < $this->maxHierarchyLength; $i++): ?>
    <td></td>
    <?php endfor; ?>
    <?php 
    $numDayTotal = 0;
    foreach ($days as $day) { 
        $numOfHours = $this->elapsedTime(ifset($dayTotals, $day, 0));
        if ($numOfHours > 0) {
			
            $dayPercentage = $this->dayDivision / za()->getConfig('day_length', 8);
            $hoursAsDays = floor ($numOfHours / $this->dayDivision) * $dayPercentage + $dayPercentage;

            // if the time was only a little above a day division, lets be nice to our
            // clients and drop off that quarter day charge
            $overDraft = fmod($numOfHours, $this->dayDivision);
            
            if ($overDraft < ($this->dayDivision * ($this->divisionTolerance/100))) {
                $hoursAsDays -= $dayPercentage;
            }

            $numDayTotal += $hoursAsDays;
			$grandTotal += $numOfHours; 
        }
        echo '<td>';
        echo $numOfHours;
        if ($numOfHours > 0) echo "<br/>($hoursAsDays)"; 
        echo "</td>";
	}

?>
    <td><?php echo $grandTotal." <br/> ($numDayTotal)";?></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
    <?php for ($i = 0; $i < $this->maxHierarchyLength; $i++): ?>
    <td></td>
    <?php endfor; ?>
	<?php foreach ($days as $day): ?>
        <th><?php $this->o($day);?></th>
    <?php endforeach;?>
    <td></td>
</tr>
</tbody>
</table>

<!-- display previous and next week links -->
<?php 
if ($this->showLinks) {
	
	$this->params['start'] = date('Y-m-d', $start - (7 * 86400)); 
	?>
	<a href="<?php echo build_url('timesheet', 'index', $this->params);?>">Previous Week</a>
	
	<?php 
	$this->params['start'] = date('Y-m-d', $end + 86400); 
	?>
	<a href="<?php echo build_url('timesheet', 'index', $this->params);?>">Next Week</a>
<?php } ?>