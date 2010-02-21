<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title><?php echo za()->getConfig('name');?> <?php if (isset($this->childView)) { echo " &mdash; "; $this->o($this->childView->title); } ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo theme_resource('reset-min.css')?>"></link>
	<link rel="stylesheet" type="text/css" href="<?php echo theme_resource('relapse-style.css')?>"></link>
	<link rel="stylesheet" type="text/css" href="<?php echo resource('time-table.css')?>"></link>

	<?php $this->style(theme_resource('jquery.ui/css/custom-theme/jquery-ui-1.7.2.custom.css'))?>
	<?php $this->style(resource('jquery-treeview/jquery.treeview.css'))?>
	<?php $this->style(theme_resource('rounded-buttons/rounded-buttons.css')); ?>
	<?php $this->style(theme_resource('jquery.simpledialog.css')); ?>
	<?php $this->style(theme_resource('flexigrid/css/flexigrid/flexigrid.css')); ?>

	<?php $this->script(theme_resource('jquery-1.4.1.min.js')); ?>
	<?php $this->script(theme_resource('jquery.ui/js/jquery-ui-1.7.2.custom.min.js')); ?>
	<?php $this->script(theme_resource('jquery.ui/js/timepicker.js')); ?>

	<?php $this->script(theme_resource('rounded-buttons/rounded-buttons.js')); ?>
	<?php $this->script(theme_resource('jquery.livequery.js')); ?>
	<?php $this->script(theme_resource('jquery.simpledialog.js')); ?>
	<?php $this->script(theme_resource('flexigrid/flexigrid.js')); ?>

	<?php $this->script(theme_resource('jqGrid/js/i18n/grid.locale-en.js')); ?>
	<?php // $this->script(theme_resource('jqGrid/js/jquery.jqGrid.min.js')); ?>
	<?php $this->script(theme_resource('jqGridDev/jquery.jqGrid.js')); ?>

	<?php $this->script(resource('time-table.js')); ?>
	<?php $this->script(resource('jquery-treeview/jquery.treeview.js'))?>
	<?php $this->script(resource('jquery-treeview/jquery.treeview.async.js'))?>
	<?php $this->script(resource('jq-plugins/auto.complete.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.cookie.js')); ?>
	<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.jeditable.js')); ?>

	<?php $this->script(resource('general.js')); ?>
	<?php $this->script(theme_resource('relapse.js')); ?>
	<?php $this->script(theme_resource('relapse-features.js')); ?>

	<style type="text/css">
	</style>

	<script type="text/javascript">
	var CURRENT_USER_ID = '<?php echo za()->getUser()->getUsername();?>';
	var NOTES_URL = '<?php echo build_url('note', 'view')?>';

	$(document).ready(function() {
		$('#ajax-loading').ajaxStart(function() {
			$(this).show();
			$(this).css('top', 15 + $().scrollTop());
		}).ajaxStop(function() {
			$(this).hide();
		});
	});
	</script>
</head>

<body>
<div id="wrap">
	<div id="top">
		<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
   		<div id="search-box" class="std">
   		<?php $this->searchBox() ?>
   		</div>
   		<?php endif; ?>

   		<div id="top-left-block" class="std">
   			<div id="session-info">

		   	</div>
   		</div>

		<div class="clear"></div>
	</div>

	<div id="top-menu" class="std">
		<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
		<div id="admin-links">
			<a href="<?php echo build_url('timesheet', 'filterSummary'); ?>">Summary Report</a> |
			<a href="<?php echo build_url('leave', 'list'); ?>">Leave</a> |
			<a href="<?php echo build_url('admin');?>">Admin</a>
		</div>
		<?php endif; ?>
		<?php echo $this->loginOutBox(); ?>
	</div>

	<div id="content">
		<div id="ajax-loading" style="position: absolute; top: 15px; right: 15px; display: none;">
	    	<img src="<?php echo resource('images/ajax-loader.gif')?>" />
    	</div>

		<div id="left">
			<div id="flash">
				<?php $this->showFlash($this->childView); ?>
			</div>
			<div id="errors">
				<?php $this->errors($this->childView); ?>
			</div>
			<div>
			    <?php echo $this->childViewContent; ?>
			</div>
		</div>


	</div>
	<div id="footer" class="clear">
	<div id="last-visited">
	<p>
	Last visited: <?php $this->o($this->u()->getLastLogin())?>
	</p>
</div>
	</div>
</div>
</body>
</html>