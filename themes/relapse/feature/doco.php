<?php
    $pid = isset($this->parent) ? $this->parent->id : 'root';
    $type = isset($this->listType) ? $this->listType : 'list';
    $tmpFeature = null;

    function displayFeature(Feature $feature, $view, &$assumptions, &$questions, $parent=null)
    {
		if (strlen($feature->assumptions)) {
			$assumptions .= '<h3>'.$view->escape($feature->title).'</h3><div class="feature-assumptions">'.nl2br($view->escape($feature->assumptions)).'</div>'."\n";
		}
		if (strlen($feature->questions)) {
			$questions .= '<h3>'.$view->escape($feature->title).'</h3><div class="feature-questions">'.nl2br($view->escape($feature->questions)).'</div>'."\n";
		}
		
		ob_start();
    	if ($feature->estimated) {
        	$view->featureEstimate += $feature->estimated;
        	$view->featureCompleted += ($feature->status == 'Complete') ? $feature->estimated : 0;
    	}
    	$lateClass = $view->project->actualstart && (strtotime($feature->created) > strtotime($view->project->actualstart)) ? 'late-feature' : '';
		$completeClass = $feature->status == 'Complete' ? 'featureComplete' : '';
		$featureId = $feature->id;
        ?>
		<li id="featurelist_<?php echo $featureId ?>" class="<?php echo $lateClass.' '.$completeClass; ?>">
			<h3>
			<?php $view->o($feature->title) ?> <?php if ($feature->estimated): ?>(<?php $view->o($feature->estimated); ?> days)<?php endif; ?>
			</h3>
			<div class="feature-body">
				<p class="feature-description"><?php $view->o($feature->description)?></p>
			</div>

			<?php
			$childFeatures = $feature->getChildFeatures();
			if (count($childFeatures)) {
				echo '<ul>';
			}
			$tmpFeature = null;

			foreach ($feature->getChildFeatures() as $child) {
				echo displayFeature($child, $view, $assumptions, $questions, $feature);
				$tmpFeature = $child;
			}

			if (count($childFeatures)) {
				echo '</ul>';
			}
		?></li><?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
    }
?>
<div class="std" id="featureDoco">
	<?php
	$this->featureEstimate = 0;
	$this->featureCompleted = 0;
	$output = '';
	$assumptions = '';
	$questions = '';
	$tmpFeature = null;
	foreach ($this->features as $feature) {
        $tmpFeature = $feature;
        $output .= displayFeature($feature, $this, $assumptions, $questions);
	}?>

	<h2>Total Estimate</h2>
	<p>
		Total estimate of <?php $this->o($this->featureEstimate);?> days work.
	</p>
	<p>
		A breakdown of high level functionality is below, followed by any
		assumptions made in coming up with these estimates, as well as questions
		that will help clarify the estimate further. 
	</p>

	<h2>Estimate Breakdown</h2>
	<ul>
	<?php 
	echo $output;
	echo '<h2>Assumptions</h2>'; echo $assumptions;
	echo '<h2>Questions</h2>'; echo $questions;
	?>
	</ul>

	


</div>
