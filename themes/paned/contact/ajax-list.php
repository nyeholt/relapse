<?php
$options = new stdClass();
$model = null;

$params['type'] = 'Contact';
$params['clientid'] = $this->client->id;

$options->url = build_url('search', 'list', $params);
// need to unset so it doesn't get bound later on in the JS
$options->dataType = 'json';
$options->colModel = array(
	array('display' => 'First Name', 'name' => 'firstname', 'width' => '150', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Last Name', 'name' => 'lastname', 'width' => '150', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Email', 'name' => 'email', 'width' => '100', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Mobile', 'name' => 'mobile', 'width' => '100', 'sortable' => true, 'align' => 'left'),
	array('display' => 'Phone', 'name' => 'directline', 'width' => '100', 'sortable' => true, 'align' => 'left'),
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
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Clients.createContact(data, '.$this->client->id.') }'),
	array('name' => 'Open', 'bclass' => 'viewbutton', 'onpress' => 'function(cmd, data) { Relapse.Clients.showContactFromGrid(data) }'),
	array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Clients.deleteContactFromGrid(data) }')
);

$this->flexiGrid('contact-list'.$this->client->id, $options);
?>