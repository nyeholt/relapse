<div class="std">
	<h2>Issues</h2>
	<?php
	$options = new stdClass();
	$params = array('json' => 1);
	$model = null;
	$exportParams = array(0, 0);
	if (isset($this->project)) {
		$model = $this->project;
		$params['projectid'] = $this->project->id;
		// hhahahggrg hack
		$exportParams[1] = $this->project->id;
	}
	if (isset($this->client)) {
		$model = $this->client;
		$params['clientid'] = $this->client->id;
		// hhahahggrg hack
		$exportParams[0] = $this->client->id;
	}

	$exportParams = implode(',', $exportParams);

	$options->url = build_url('issue', 'list', $params);
	$options->dataType = 'json';
	$options->colModel = array(
		array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
		array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
		array('display' => 'Est Hours', 'name' => 'estimated', 'width' => '40', 'sortable' => true, 'align' => 'left'),
		array('display' => 'Status', 'name' => 'status', 'width' => '100', 'sortable' => true, 'align' => 'left'),
		array('display' => 'Severity', 'name' => 'severity', 'width' => '100', 'sortable' => true, 'align' => 'left'),
		array('display' => 'User', 'name' => 'userid', 'width' => '140', 'sortable' => true, 'align' => 'left'),
		array('display' => 'Updated', 'name' => 'updated', 'width' => '140', 'sortable' => true, 'align' => 'left')
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
	$options->height = 200;
	$options->pagestat = 'Displaying: {from} to {to} of {total} items.';
	$options->onError = "function() { if (true) {}; alert(data); }";
	$options->buttons = array(
		array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.tableCommand(cmd, data, "'.build_url('issue', 'edit', $params).'") }'),
		array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.editIssue(data) }'),
		array('name' => 'Start', 'bclass' => 'timingbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.startTiming(data) }'),
		array('name' => 'New Task', 'bclass' => 'taskbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.createTask(data) }'),
		array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.tableCommand(cmd, data) }'),
		array('name' => 'Export', 'bclass' => 'exportbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.exportIssues('.$exportParams.') }')
	);
	$this->flexiGrid('issue-list', $options);
	?>
</div>