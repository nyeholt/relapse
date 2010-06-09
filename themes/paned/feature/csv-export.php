Title,Description,Estimate
<?php
function outputFeature($feature, $view) {
	$view->csv($feature->title);
	echo ',';
	$view->csv($feature->description);
	echo ',';
	if ($feature->estimated) {
		$view->featureEstimate += $feature->estimated;
		$view->featureCompleted += ($feature->status == 'Complete') ? $feature->estimated : 0;
		echo $feature->estimated;
	}
	echo "\n";

	$children = $feature->getChildFeatures();
	if (count($children)) {
		foreach ($children as $child) {
			outputFeature($child, $view);
		}
	}
}

$this->featureEstimate = 0;
$view->featureCompleted = 0;
foreach ($this->features as $feature) {
	outputFeature($feature, $this);
}
echo ',Total,' .$this->featureEstimate;
?>