<?php
$options = new stdClass();

$params['type'] = 'Project';
$params['deleted'] = 0;
$params['ismilestone'] = 0;

if (isset($this->client)) {
	$params['clientid'] = $this->client->id;
}

$options->url = build_url('search', 'list', $params);

$options->dataType = 'json';
$options->colModel = array(
	array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Time Spent (hours)', 'name' => 'currenttime', 'width' => '50', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Estimate (hours)', 'name' => 'estimated', 'width' => '50', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Budget (hours)', 'name' => 'budgeted', 'width' => '50', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Description', 'name' => 'description', 'width' => '400', 'sortable' => false, 'align' => 'left'),
);

$options->searchitems = array(
	array('display' => 'All', 'name' => 'all'),
);

$options->sortname = 'id';
$options->sortorder = 'desc';
$options->usepager = true;
$options->useRp = true;
$options->rp = 10;
$options->singleSelect = true;
$options->width = 'auto';
$options->height = 'auto';
$options->pagestat = 'Displaying: {from} to {to} of {total} items.';
$options->onError = "function() { if (true) {}; alert(data); }";
$options->buttons = array(
	array('name' => 'Open', 'bclass' => 'viewbutton', 'onpress' => 'function(cmd, data) { $(".trSelected",data).each (function () { var id = $(this).attr("id").replace("row", ""); if (id > 0) { location.href = BASE_URL + "project/view/id/" + id; } }); }')
);
$this->flexiGrid('projects-list', $options);