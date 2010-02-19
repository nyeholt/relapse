<div class="std">
	<h2>Issues</h2>
	<?php
	$options = new stdClass();
	$params = array('json' => 1);
	$model = null;
	if ($this->project) {
		$model = $this->project;
		$params['projectid'] = $this->project->id;
	}
	if ($this->client) {
		$model = $this->client;
		$params['clientid'] = $this->client->id;
	}

	$options->url = build_url('issue', 'list', $params);
	$options->dataType = 'json';
	$options->colModel = array(
		array('display' => 'ID', 'name' => 'id', 'width' => '20', 'sortable' => true, 'align' => 'center'),
		array('display' => 'Title', 'name' => 'title', 'width' => '400', 'sortable' => true, 'align' => 'left'),
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
		array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.tableCommand(cmd, data) }'),
		array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.tableCommand(cmd, data) }'),
		array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.tableCommand(cmd, data) }'),
		array('name' => 'Export', 'bclass' => 'exportbutton', 'onpress' => 'function(cmd, data) { Relapse.Issues.tableCommand(cmd, data) }')
	);
	$this->flexiGrid('issue-list', $options);
	?>
</div>

<script type="text/javascript">
	$().ready(function () {
		Relapse.IssueManager.prototype.tableCommand = function (cmd, grid) {
			if (cmd == 'New') {
				Relapse.createDialog('issuedialog', {title: 'Add new Issue', url: '<?php echo build_url('issue', 'edit', $params)?>'});
			} else if (cmd == 'Edit') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					Relapse.createDialog('issuedialog', {title: 'Edit Issue', url: '<?php echo build_url('issue', 'edit')?>id/'+id});
				});
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (confirm("Are you sure you want to delete this?")) {
						$.post('<?php echo build_url('issue', 'delete', array('__validation_token' => $this->requestValidator(true))) ?>', {id: id}, function () {
							$('.pReload',grid).click();
						});
					}
				});
			} else if (cmd == 'Export') {
				location.href = '<?php echo build_url('issue', 'csvExport', array('unlimited' => 1)) ?>';
			}
		}
	});
</script>