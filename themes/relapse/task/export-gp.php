<?php 
$sep = "";
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
}
?>