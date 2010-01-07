<?php 
    $pid = isset($this->parent) ? $this->parent->id : 'root';
    $type = isset($this->listType) ? $this->listType : 'list';
    $tmpFeature = null;
    
    function displayFeature(Feature $feature, $view, $pad = 0)
    {
    	if ($feature->estimated) {
        	$view->featureEstimate += $feature->estimated;
        	$view->featureCompleted += ($feature->complete) ? $feature->estimated : 0;
    	}
    	$bgcolor = strtotime($feature->created) > strtotime($view->project->started) ? ' style="background-color: #acc"' : '';
        ?>
        <!-- Feature created <?php echo $feature->created?> and project started at <?php echo $view->project->started?> -->
        <tr <?php echo $bgcolor ?>>
        <td>
        <?php echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $pad); ?>
        <?php if ($feature->complete): ?>
        <img class="small-icon" src="<?php echo resource('images/accept.png')?>" />
        <?php endif;?>
        <a title="Show Details" href="<?php echo build_url('feature', 'edit', array('id' => $feature->id, 'projectid'=>$feature->projectid))?>"><?php $view->o($feature->title)?> </a> 
        </td>
        <td><a href="<?php echo build_url('project', 'view', array('id' => $feature->milestone));?>"><?php $view->o($feature->getMilestoneTitle())?></a></td>
        <td style="text-align: center">

        <?php $view->o($feature->estimated)?>
        
        </td>
        <td>
        <input class="action-icon" type="checkbox" value="<?php $view->o($feature->id)?>" name="createfrom[]" />
	  	<a title="Delete Feature" href="#" onclick="if (confirm('Are you sure')) location.href='<?php echo build_url('feature', 'delete', array('id' => $feature->id))?>'; return false;"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
	  	<a title="Add Child Feature" href="<?php echo build_url('feature', 'edit', array('parent' => $feature->id, 'projectid'=>$feature->projectid))?>"><img class="small-icon" src="<?php echo resource('images/page_copy.png')?>" /></a>
        </td>
        </tr>
        <?php 
        $numberOfChildren = 0;
        $tmpFeature = null;
        foreach ($feature->getChildFeatures() as $child) {
            ++$numberOfChildren;
            displayFeature($child, $view, $pad + 1);   
            $tmpFeature = $child;
        }
        if ($numberOfChildren > 1) {
            ?> 
            <tr>
            <td>
            <?php echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $pad + 1); ?>
            <a href="<?php echo build_url('feature', 'orderfeatures', array('id'=>$feature->id))?>" title="Edit feature order" >Order Features</a>
			</td>
            <td></td><td></td><td></td>
            </tr>
            <?php 
        }
    }
?>

<a href="<?php echo build_url('feature', 'read', array('projectid' => $this->project->id))?>">Read Feature Document</a>

<table class="item-table" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
		<th width="60%">Title</th>
		<th width="20%">Target Milestone</th>
		<th width="10%">Estimate</th>
		<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$this->featureEstimate = 0;
	$this->featureCompleted = 0;
	foreach ($this->features as $feature) {
        $tmpFeature = $feature;
        displayFeature($feature, $this);
        
	}?>
	<tr>
		<td>Total</td>
		<td></td>
		<td><?php $this->o($this->featureCompleted)?> / <?php $this->o($this->featureEstimate);?></td>
		<td></td>
	</tr>
	</tbody>
</table>
<script type="text/javascript">
	$().ready(function() {
		var index = $("#features-index");
		
		if (index) {
			index.html("Features (<?php echo count($this->features)?>)");
		}
	});
</script>
<?php if($tmpFeature != null): ?>
<p>
<a href="<?php echo build_url('feature', 'orderfeatures', array('projectid'=>$tmpFeature->projectid))?>" title="Edit feature order" >Order Features</a>
</p>

<?php endif; ?>