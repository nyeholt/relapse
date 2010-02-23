<div class="std">
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

$options->sortname = 'id';
$options->sortorder = 'desc';
$options->usepager = true;
$options->singleSelect = true;
$options->useRp = true;
$options->rp = 20;
$options->width = 'auto';
$options->height = '300';
$options->pagestat = 'Displaying: {from} to {to} of {total} items.';
$options->onError = "function() { if (true) {}; alert(data); }";
$options->buttons = array(
	array('name' => 'View', 'bclass' => 'viewbutton', 'onpress' => 'function(cmd, data) { Relapse.clientCommand(cmd, data) }'),
	array('name' => 'New', 'bclass' => 'newbutton', 'onpress' => 'function(cmd, data) { Relapse.clientCommand(cmd, data) }'),
	array('name' => 'Edit', 'bclass' => 'editbutton', 'onpress' => 'function(cmd, data) { Relapse.clientCommand(cmd, data) }'),
	array('name' => 'Delete', 'bclass' => 'deletebutton', 'onpress' => 'function(cmd, data) { Relapse.clientCommand(cmd, data) }')
);

$this->flexiGrid('client-list', $options);
?>
</div>
<script type="text/javascript">
	$().ready(function () {
		Relapse.clientCommand = function (cmd, grid) {
			if (cmd == 'New') {
				Relapse.createDialog('clientdialog', {title: 'Create Feature', url: '<?php echo build_url('client', 'edit')?>'});
			} else if (cmd == 'View') {

				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0) {
						window.location.href = '<?php echo build_url('client', 'view')?>id/'+id;
					}
				});
			} else if (cmd == 'Edit') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0) {
						Relapse.createDialog('clientdialog', {title: 'Create Feature', url: '<?php echo build_url('client', 'edit')?>id/'+id});
					}
				});
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0 && confirm("Are you sure you want to delete this?")) {
						$.post('<?php echo build_url('client', 'delete', array('_ajax' => 1, '__validation_token' => $this->requestValidator(true))) ?>', {id: id}, function () {
							$('.pReload',grid).click();
						});
					}
				});
			}
		}
	});
</script>
