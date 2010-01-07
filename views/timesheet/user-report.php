<?php 
unset($this->params['controller']);
unset($this->params['action']);
unset($this->params['module']);
?>

<h3>
Summary Report, from <?php $this->o($this->startDate)?> to <?php $this->o($this->endDate); ?>. 
</h3>
<p>Click a name to see 
detailed breakdown for that user</p>
<?php 
$curr = $start = strtotime($this->startDate);
$end = strtotime($this->endDate);

?>
<br/><br/>
<table class="item-table" cellpadding="0" cellspacing="0">
<thead>
<tr>
    <th width="20%">User</th>
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

foreach ($this->taskInfo as $username => $taskDetails) {
	// store how much time depending on the task category
	$inCategory = array();
	foreach ($taskDetails as $info) {
		$total = 0;
		foreach ($info->days as $dayTime) {
			$total += $dayTime;	
		}
		
		$current = ifset($inCategory, $info->taskcategory, 0);
		$inCategory[$info->taskcategory] = $current + $total;
	}
?>
<tr>
	<td>
	<a href="<?php echo build_url('timesheet', 'index', array('start'=> date('Y-m-d', strtotime($this->startDate)),'username'=>$username))?>"><?php $this->o($username); ?></a>
	</td>
	<?php 
	$userTotal = 0;
	foreach ($this->categories as $cat): 
		$dayTime = ifset($inCategory, $cat);
		$userTotal += $dayTime;
	?>
	<td>
		<?php $this->o($this->elapsedTime($dayTime)); ?>
	</td>
	<?php endforeach; ?>
	<td>
		<?php $this->o($this->elapsedTime($userTotal));?>
	</td>
</tr>
<?php
	
	$grandTotal += $userTotal;
} 
?>
<tr class="total-row">
    <td>Totals</td>
    <?php foreach ($this->categories as $cat):?>
	<td></td>
    <?php endforeach; ?>
    <td>    
    <?php echo $this->elapsedTime($grandTotal); ?>
    </td>
</tr>
</tbody>
</table>

<!-- display previous and next week links -->
<?php 
if ($this->showLinks) {
	
	$this->params['start'] = date('Y-m-d', $start - (7 * 86400)); 
	?>
	<a href="<?php echo build_url('timesheet', 'summary', $this->params);?>">Previous Week</a>
	
	<?php 
	$this->params['start'] = date('Y-m-d', $end + 86400); 
	?>
	<a href="<?php echo build_url('timesheet', 'summary', $this->params);?>">Next Week</a>
<?php } ?>