<?php
$options = new stdClass();
$model = null;
$params['type'] = 'Feature';
$params['projectid'] = $this->project->id;
$params['milestone'] = $childProject->id;

$options->url = build_url('search', 'list', $params);
// need to unset so it doesn't get bound later on in the JS
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
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Features.tableCommand(cmd, data, "'.build_url('feature', 'edit', $params).'") }'),
	array('name' => 'Start', 'bclass' => 'timingbutton', 'onpress' => 'function(cmd, data) { Relapse.Features.startTiming(data) }'),
	array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.Features.tableCommand(cmd, data) }'),
	array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Features.tableCommand(cmd, data) }')
);
$this->flexiGrid('feature-list'.$childProject->id, $options);
?>