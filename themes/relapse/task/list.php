<?php
$options = new stdClass();
$model = null;

$options->url = isset($taskListUrl) ? $taskListUrl : build_url('task', 'list');
$options->dataType = 'json';
$options->colModel = array(
	array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
	array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Hours Spent', 'name' => 'timespent', 'width' => '80', 'sortable' => true, 'align' => 'right'),
	array('display' => 'Due', 'name' => 'due' , 'width' => '170', 'sortable' => true, 'align' => 'right'),
	array('display' => 'Percent Complete', 'name' => 'getPercentage', 'width' => '100', 'sortable' => false, 'align' => 'left'),
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
$options->preProcess = "function(data) { return Relapse.Tasks.preProcessTableData(data) }";
$options->buttons = array(
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Tasks.tableCommand(cmd, data) }'),
	array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.Tasks.tableCommand(cmd, data) }'),
	array('name' => 'Start', 'bclass' => 'timingbutton', 'onpress' => 'function(cmd, data) { Relapse.Tasks.tableCommand(cmd, data) }'),
	array('name' => 'Timesheet', 'bclass' => 'timesheetbutton', 'onpress' => 'function(cmd, data) { Relapse.Tasks.tableCommand(cmd, data) }'),
	array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Tasks.tableCommand(cmd, data) }')
);
$this->flexiGrid('user-task-list', $options);
?>
