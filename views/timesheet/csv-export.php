<?php 
unset($this->params['controller']);
unset($this->params['action']);
unset($this->params['module']);
?>

<?php 
$days = array();
$curr = $start = strtotime($this->startDate);
$end = strtotime($this->endDate);

while ($curr < $end) {
    $days[] = date('D d/m', $curr);
    $curr += 86400;
}
?>
User,Project ID,<?php

	for ($i = 0; $i < $this->maxHierarchyLength; $i++): 
    	$output = "Project";
    	if ($i == $this->maxHierarchyLength - 1) {
    		$output = "Milestone";
    	} else if ($i == 0) {
    		$output = "Client";
    	}
    $this->csv($output); echo ",";
    endfor; ?>Task,Task ID,Category,<?php 
    foreach ($days as $day):
        $this->csv($day); echo ",";
    endforeach;?>Total
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
<?php $this->csv($info->user)?>,<?php $this->csv($immediateParent != null ? $immediateParent->id : ''); ?>,<?php $this->csv($info->clienttitle)?>,<?php 
    for ($i = 0; $i < $this->maxHierarchyLength - 2; $i++) {
    // we always need to output enough <td></td> to make sure that the table alignment matches properly
	// which is why we go to the max length of the hierarchy, not just as many parent projects there are
	// we cut 2 off the length because we're ignoring the milestone and the client that are also in the hierarchy
    	if (isset($parentProjects[$i])) { 
    		$project = $parentProjects[$i];
    		$this->csv($project->title); echo ",";
		} else {
			echo ",";
		}
	} 
	if ($milestone != null): ?><?php $this->csv($milestone->title)?><?php endif; ?>,<?php $this->csv($info->title)?>,<?php $this->csv($info->taskid)?>,<?php $this->csv($info->taskcategory)?>,<?php $taskTotal = 0; 
	foreach ($days as $day):
        $taskTotal += ifset($info->days, $day, 0); 
        $dayTotal = ifset($dayTotals, $day, 0);
        $dayTotals[$day] = $dayTotal + ifset($info->days, $day, 0);
        echo $this->elapsedTime(ifset($info->days, $day, 0)); echo ",";
    endforeach;
    echo $this->elapsedTime($taskTotal); 
    echo "\r\n";
}
?>
Totals,,,,,<?php 

	for ($i = 0; $i < $this->maxHierarchyLength; $i++):
	    echo ",";
	endfor; 
	
    $numDayTotal = 0;
    foreach ($days as $day) { 
        $numOfHours = $this->elapsedTime(ifset($dayTotals, $day, 0));
        if ($numOfHours > 0) {
			$grandTotal += $numOfHours; 
        }
        
        echo $numOfHours;
        
        echo ",";
	}

?><?php echo $grandTotal; echo "\r\n"?>,,,,,<?php 
	for ($i = 0; $i < $this->maxHierarchyLength; $i++):
	    echo ",";
	endfor; 

	foreach ($days as $day): 
        $this->csv($day); echo ",";
    endforeach;
?>