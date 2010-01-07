
INVOICE
Marcus Nyeholt       ABN 25 573 761 909

<?php echo date('F j, Y', strtotime($this->invoice->created)); ?>


TO:
<?php echo $this->client->title; ?>


------------------------------------------------------------------------------
Invoice # <?php echo $this->invoice->id; ?>

------------------------------------------------------------------------------

<?php
$totalHours = 0;
echo str_pad("Task", 60);
echo "Hours";
echo "\n";

foreach ($this->records as $task) {
	// Before doing anything, we remove 1/4 of an hour
	// so that we don't make 3.01 hours 3.5
	$taskTime = $task->timespent - (60 * 10);
	if ($taskTime > 0) {
		
		echo str_pad($task->title, 60);
		
		// First divide by 360 to get hours * 10
		// so that we can round up to nearest 5
		$val = $taskTime / 360;
		$val = (5 * ceil($val/5)) / 10;
		echo $val."\n";
		$totalHours += $val;
	}	
}

echo str_pad("", 60);
echo $totalHours;
echo "\n\n";
echo str_pad("Total ", 60);
echo '$'.($this->project->rate * $totalHours);
?>

All Prices AUD

------------------------------------------------------------------------------

Payments may be made via Direct Deposit to 

Marcus Nyeholt
BSB: 083-457
Account Number: 54-376-9510
