<?php
$options = new stdClass();

$params['type'] = 'Project';
$params['deleted'] = 0;
$params['ismilestone'] = 0;

$cid = 0;
if (isset($this->client)) {
	$params['clientid'] = $this->client->id;
	$cid = $this->client->id;
}

$options->url = build_url('search', 'list', $params);

$options->dataType = 'json';
$options->colModel = array(
	array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Time Spent', 'name' => 'currenttime', 'width' => '50', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Estimate', 'name' => 'featureestimate', 'width' => '50', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Budget', 'name' => 'budgeted', 'width' => '50', 'sortable' => true, 'align' => 'center'),
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
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.addToPane("RightPane", BASE_URL + "project/edit/clientid/'.$cid.'", "New Project"); }'),
	array('name' => 'Open', 'bclass' => 'viewbutton', 'onpress' => 'function(cmd, data) { $(".trSelected",data).each (function () { var id = $(this).attr("id").replace("row", ""); var rowTds = $(this).find("td"); var title = $(rowTds[1]).find("div").text(); if (id > 0) { Relapse.addToPane("CenterPane", BASE_URL + "project/view/id/" + id, title); } }); }')
);
$this->flexiGrid('projects-list', $options);