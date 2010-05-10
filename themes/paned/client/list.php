<?php
$options = new stdClass();
$model = null;
$params['type'] = 'Client';

$options->url = build_url('search', 'list', $params);
// need to unset so it doesn't get bound later on in the JS
$options->dataType = 'json';
$options->colModel = array(
	array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Type', 'name' => 'relationship', 'width' => '120', 'sortable' => true, 'align' => 'left'),
);

$options->searchitems = array(
	array('display' => 'All', 'name' => 'all'),
);

$options->sortname = 'title';
$options->sortorder = 'asc';
$options->usepager = true;
$options->singleSelect = true;
$options->useRp = true;
$options->rp = 20;
$options->width = 'auto';
$options->height = '300';
$options->pagestat = 'Displaying: {from} to {to} of {total} items.';
$options->onError = "function() { if (true) {}; alert(data); }";
$options->buttons = array(
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Projects.newClient(cmd, data) }'),
	array('name' => 'View', 'bclass' => 'viewbutton', 'onpress' => 'function(cmd, data) { Relapse.Projects.openClient(cmd, data) }'),
	array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.Projects.editClient(cmd, data) }'),
	array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Projects.deleteClient(cmd, data) }')
);

$this->flexiGrid('client-list', $options);
?>