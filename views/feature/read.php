<div id="parent-links">
    <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#features'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>

<div class="feature-document">
<h1><?php $this->o($this->project->title)?></h1> 
<div>[<a href="javascript: void(0);" id="enable-editing">Allow Editing</a>]</div>
<?php $this->wikiCode($this->project->description); ?>

<!-- generated table of contents -->

<?php
echo '<ol>';
foreach ($this->features as $feature) {
	featureTableOfContents($feature, $this);
}
echo '</ol>';

foreach ($this->features as $feature) {
	displayFeature($feature, $this);
}

function featureTableOfContents(Feature $feature, $view, $depth = 0)
{
	?>
	<li><a href="#feature-<?php echo $feature->id?>-title"><?php $view->o($feature->title)?></a></li>
	<?php
	$children = $feature->getChildFeatures();
	if (count($children)) {
		echo '<ol>';
		foreach ($children as $child) {
			featureTableOfContents($child, $view, $depth + 1);
		}
		echo '</ol>';
	}
}

function displayFeature(Feature $feature, $view, $depth = 1)
{
	?>
	<div class="feature-block">
	<h<?php echo $depth?>><span class="editable-feature" name="feature-<?php echo $feature->id?>-title" id="feature-<?php echo $feature->id?>-title"><?php $view->o($feature->title)?></span>
	<?php if ($feature->estimated > 0): ?>
	(<?php $view->o($feature->estimated); ?> days)
	<?php endif; ?>
	<a href="<?php echo build_url('feature', 'edit', array('id' => $feature->id))?>">&raquo;</a></h<?php echo $depth?>>
	
	<div class="feature-label">Description</div>
	<div class="feature-description editable-feature" id="feature-<?php echo $feature->id?>-description">
	<?php $view->wikiCode($feature->description)?>
	</div>

	<div class="feature-label">Implentation Plan</div>
	<div class="feature-implementation editable-feature" id="feature-<?php echo $feature->id?>-implementation">
	<?php $view->wikiCode($feature->implementation)?>
	</div>

	<?php
	foreach ($feature->getChildFeatures() as $feature) {
		displayFeature($feature, $view, $depth + 1);
	}
	?>
	</div>
	<?php 
}
?>

</div>

<script type="text/javascript">

$('#enable-editing').click(function() {
	$(this).html("Editing mode enabled");
	$('.editable-feature').each(function () {
		$(this).editable('<?php echo build_url('feature', 'readUpdate') ?>', { 
	         loadurl  : '<?php echo build_url('feature', 'loadField')?>',
	         type    : 'textarea',
	         submit  : 'Done',
	         indicator : 'Saving...', 
	         height  : '200px',
	         style   : 'font-size: 8pt; font-family: Tahoma;',
	         onblur : 'ignore'
		});
	});
});

</script>