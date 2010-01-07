ID,Title,Description,Status,Severity,Type,Created By,Assigned To,Last Updated,Project,Notes
<?php // figure out what date range we're looking at so we know what 'old' status we should be looking at 
$previousDate = strtotime("-7 days");
?>
<?php foreach ($this->issues as $issue) {
	$this->csv($issue->id); echo ","; 
	$this->csv($issue->title); echo ',';
	$this->csv($issue->description); echo ','; 
    //$this->csv(sprintf('%.2f', $issue->elapsed)); echo ',';
    //$this->csv(sprintf('%.2f', $issue->estimated)); echo ','; 
	$this->csv($issue->status); echo ','; 
	$this->csv($issue->severity); echo ','; 
	$this->csv($issue->issuetype); echo ',';
	$this->csv($issue->creator); echo ',';
	$this->csv($issue->userid); echo ',';
	$this->csv(date("d/m/Y H:i", strtotime($issue->updated))); echo ',';
	
	$this->csv($issue->projectname);echo ',';
	
	$notes = $issue->getNotes();
	$noteText = '';
	foreach ($notes as $note) {
		$noteText .= $note->userid.' on '.$note->created.':'."\r\n";
		$noteText .= $note->note."\r\n\r\n";
	}
	$this->csv($noteText);
	
	echo "\r\n";
	 
}
/*$sep = "";
foreach ($this->export->getHeaderRow() as $header) {
    echo $sep,$this->csv($header);
    $sep = ",";
}

echo "\n";

while (($row = $this->export->getNextDataRow()) != null) {
    $sep = "";
    foreach ($row as $value) {
        echo $sep, $this->csv($value);
        $sep = ",";
    }
echo "\n";
}*/
?>