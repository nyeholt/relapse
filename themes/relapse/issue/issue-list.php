<div class="std">
	<?php
	$options = new stdClass();
	$options->url = build_url('issue', 'list', array('projectid' => $this->project->id, 'json' => 1));
	$options->dataType = 'json';
	$options->colModel = array(
		array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
		array('display' => 'Title', 'name' => 'title', 'width' => '300', 'sortable' => true, 'align' => 'left'),
		array('display' => 'Status', 'name' => 'status', 'width' => '80', 'sortable' => true, 'align' => 'left'),
		array('display' => 'User', 'name' => 'userid', 'width' => '80', 'sortable' => true, 'align' => 'left'),
		array('display' => 'Updated', 'name' => 'updated', 'width' => '120', 'sortable' => true, 'align' => 'left')
	);
	$options->searchitems = array(
		array('display' => 'ID', 'name' => 'id'),
		array('display' => 'Title', 'name' => 'title', 'isdefault' => true)
	);
	$options->sortname = 'id';
	$options->sortorder = 'desc';
	$options->userpager = true;
	$options->title = 'Issues';
	$options->useRp = true;
	$options->rp = 15;
	$options->rpOptions = array(10,15,20,25,40);
	$options->showTableToggleBtn = true;
	$options->width = 'auto';
	$options->height = 200;
	$options->pagestat = 'Displaying: {from} to {to} of {total} items.';
	$options->onError = "function() { if (true) {}; alert(data); }";
	$options->buttons = array(
		array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'alert("stuff")')
	);
	$this->flexiGrid('issue-list', $options);
	?>
</div>