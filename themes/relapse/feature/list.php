<?php
    $pid = isset($this->parent) ? $this->parent->id : 'root';
    $type = isset($this->listType) ? $this->listType : 'list';
    $tmpFeature = null;

    function displayFeature(Feature $feature, $view, $pad = 0)
    {
		ob_start();
    	if ($feature->estimated) {
        	$view->featureEstimate += $feature->estimated;
        	$view->featureCompleted += ($feature->complete) ? $feature->estimated : 0;
    	}
    	$lateClass = $view->project->actualstart && (strtotime($feature->created) > strtotime($view->project->actualstart)) ? 'late-feature' : '';
		$completeClass = $feature->complete ? 'featureComplete' : '';
        ?>
		<div class="featureDepth<?php echo $pad?>">
			<!-- Feature created <?php echo $feature->created?> and project started at <?php echo $view->project->actualstart?> -->
			<div class="feature-title <?php echo $lateClass.' '.$completeClass; ?>">
				<?php $view->dialogPopin('featuredialog', '<h2>'.$view->escape($feature->title).'</h2>', build_url('feature', 'edit', array('id' => $feature->id, 'projectid'=>$feature->projectid)), array('title'=> 'Edit Feature')) ?>

				<a title="Delete Feature" href="#" onclick="if (confirm('Are you sure')) $.post('<?php echo build_url('feature', 'delete', array('id' => $feature->id))?>', function () { location.reload(false) }); return false;"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>

				<?php $view->dialogPopin('featuredialog', '<img src="'.resource('images/page_copy.png').'" />', build_url('feature', 'edit', array('parent' => $feature->id, 'projectid'=>$feature->projectid))) ?>
			</div>

			<?php if ($feature->estimated): ?>

			<div class="feature-body">
				<div class="feature-estimate">
					<p class="feature-milestone"><?php $view->o($feature->getMilestoneTitle())?></p>
					<p class="estimate">
					<?php $view->o($feature->estimated)?>
					</p>
				</div>
				<div class="feature-content">
					<p><?php $view->o($feature->description)?></p>
					<p><?php $view->o($feature->assumptions)?></p>
					<p><?php $view->o($feature->questions)?></p>
				</div>
			</div>
			<?php endif; ?>
			
			<?php
			$numberOfChildren = 0;
			$tmpFeature = null;
			foreach ($feature->getChildFeatures() as $child) {
				++$numberOfChildren;
				echo displayFeature($child, $view, $pad + 1);
				$tmpFeature = $child;
			}
			if ($numberOfChildren > 1) {
				?>
				<div class="reoder-features featureDepth<?php echo ($pad + 1)?>">
				<a href="<?php echo build_url('feature', 'orderfeatures', array('id'=>$feature->id))?>" title="Edit feature order" >ReOrder Features</a>
				</div>
				<?php
			}
		?></div><?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
    }
?>
<div class="std">

	<?php
	$this->featureEstimate = 0;
	$this->featureCompleted = 0;
	$output = '';
	foreach ($this->features as $feature) {
        $tmpFeature = $feature;
        $output .= displayFeature($feature, $this);

	}?>

	<a href="<?php echo build_url('project', 'view', array('id' => $this->project->id))?>">&laquo;</a>
	
	<p class="estimate">
		<?php $this->o($this->featureCompleted)?> / <?php $this->o($this->featureEstimate);?>
	</p>

	<?php echo $output; ?>

	<?php if($tmpFeature != null): ?>
	<p>
	<a href="<?php echo build_url('feature', 'orderfeatures', array('projectid'=>$tmpFeature->projectid))?>" title="Edit feature order" >ReOrder Features</a>
	</p>
	<?php endif; ?>
</div>

