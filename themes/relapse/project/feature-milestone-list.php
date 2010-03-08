<?php
$options = new stdClass();
$model = null;
$params['type'] = 'Feature';
$params['projectid'] = $this->project->id;
$params['milestone'] = $childProject->id;

$options->url = build_url('search', 'list', $params);
// need to unset so it doesn't get bound later on in the JS
unset($params['milestone']);
$options->dataType = 'json';
$options->colModel = array(
	array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Estimated', 'name' => 'estimated', 'width' => '100', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Status', 'name' => 'status', 'width' => '100', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Percent Complete', 'name' => 'getPercentageComplete', 'width' => '100', 'sortable' => false, 'align' => 'left'),
);

$options->searchitems = array(
	array('display' => 'All', 'name' => 'all'),
);

$options->sortname = 'id';
$options->sortorder = 'desc';
$options->usepager = true;
$options->singleSelect = true;
$options->width = 'auto';
$options->height = 'auto';
$options->pagestat = 'Displaying: {from} to {to} of {total} items.';
$options->onError = "function() { if (true) {}; alert(data); }";
$options->buttons = array(
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Features.tableCommand(cmd, data, '.$childProject->id.') }'),
	array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.Features.tableCommand(cmd, data, '.$childProject->id.') }'),
	array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Features.tableCommand(cmd, data, '.$childProject->id.') }')
);
$this->flexiGrid('feature-list'.$childProject->id, $options);
?>

<script type="text/javascript">
	// ugly, because it gets included every time per milestone when it only needs to be done once...
	$().ready(function () {
		Relapse.FeatureManager.prototype.tableCommand = function (cmd, grid, milestone) {
			if (cmd == 'New') {
				Relapse.createDialog('featuredialog', {title: 'Create Feature', url: '<?php echo build_url('feature', 'edit', $params)?>milestone/' + milestone});
			} else if (cmd == 'Edit') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0) {
						Relapse.createDialog('featuredialog', {title: 'Edit Feature', url: '<?php echo build_url('feature', 'edit')?>id/'+id});
					}
				});
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0 && confirm("Are you sure you want to delete this?")) {
						$.post('<?php echo build_url('feature', 'delete', array('_ajax' => 1, '__validation_token' => $this->requestValidator(true))) ?>', {id: id}, function () {
							$('.pReload',grid).click();
						});
					}
				});
			}
		}
	});
</script>