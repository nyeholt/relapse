<?php
    $pid = isset($this->parent) ? $this->parent->id : 'root';
    $type = isset($this->listType) ? $this->listType : 'list';
    $tmpFeature = null;

    function displayFeature(Feature $feature, $view, $parent=null)
    {
		ob_start();
    	if ($feature->estimated) {
        	$view->featureEstimate += $feature->estimated;
        	$view->featureCompleted += ($feature->complete) ? $feature->estimated : 0;
    	}
    	$lateClass = $view->project->actualstart && (strtotime($feature->created) > strtotime($view->project->actualstart)) ? 'late-feature' : '';
		$completeClass = $feature->complete ? 'featureComplete' : '';
		$featureId = $feature->id;
        ?>
		<li id="<?php echo $featureId ?>">
			<input type="hidden" name="featureId" value="<?php echo $feature->id ?>" />
			<!-- Feature created <?php echo $feature->created?> and project started at <?php echo $view->project->actualstart?> -->
			<div class="feature-title <?php echo $lateClass.' '.$completeClass; ?>">
				<h2>
				<?php $view->dialogPopin('featuredialog', $view->escape($feature->title), build_url('feature', 'edit', array('id' => $feature->id, 'projectid'=>$feature->projectid)), array('title'=> 'Edit Feature')) ?>
				</h2>

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
			$childFeatures = $feature->getChildFeatures();
			if (count($childFeatures)) {
				echo '<ul>';
			}
			$tmpFeature = null;

			foreach ($feature->getChildFeatures() as $child) {
				echo displayFeature($child, $view, $feature);
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
<div class="std" id="featureList">
	
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

	<p style="margin-bottom: 1em;">
	<input type="button" class="abutton enableReorder" value="Sort" />
	<input type="button" class="abutton disableReorder" value="Done" style="display:none;" />
	<input type="button" class="abutton saveOrder" value="Save" style="display:none;" />
	</p>
	<ul>
	<?php echo $output; ?>
	</ul>
	<?php if($tmpFeature != null): ?>
	<p>
	<input type="button" class="abutton enableReorder" value="Sort" />
	<input type="button" class="abutton disableReorder" value="Done" style="display:none;" />
	<input type="button" class="abutton saveOrder" value="Save" style="display:none;" />
	</p>
	<?php endif; ?>

	<form id="featureSort" method="post" action="<?php echo build_url('feature', 'saveOrder') ?>">
	</form>
</div>

