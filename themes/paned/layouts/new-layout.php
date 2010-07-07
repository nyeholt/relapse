<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title><?php echo za()->getConfig('name');?> <?php if (isset($this->childView)) { echo " &mdash; "; $this->o($this->childView->title); } ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo theme_resource('reset-min.css')?>"></link>
	
	<link rel="stylesheet" type="text/css" href="<?php echo resource('time-table.css')?>"></link>

	<?php $this->style(theme_resource('jquery.ui/css/custom-theme/jquery-ui-1.7.2.custom.css'))?>
	<?php $this->style(resource('jquery-treeview/jquery.treeview.css'))?>
	<?php $this->style(theme_resource('rounded-buttons/rounded-buttons.css')); ?>
	<?php $this->style(theme_resource('jquery.simpledialog.css')); ?>
	<?php $this->style(theme_resource('flexigrid/css/flexigrid/flexigrid.css')); ?>
	<?php $this->style(theme_resource('jquery.ui/css/ui-lightness/ui.timepickr.css'))?>
	<?php $this->style(theme_resource('jquery.timepickr.css')); ?>
	<?php $this->style(theme_resource('layout-default-latest.css')); ?>
	
	<?php $this->style(theme_resource('style.css')); ?>

	<?php $this->script(theme_resource('jquery-1.4.2.min.js')); ?>
	<?php $this->script(theme_resource('jquery.ui/js/jquery-ui-1.7.2.custom.min.js')); ?>
	<?php $this->script(theme_resource('jquery.metadata.js')); ?>
	<?php $this->script(theme_resource('jquery.validate.pack.js')); ?>
	<?php $this->script(theme_resource('jquery.timepickr.min.js')); ?>

	<?php $this->script(theme_resource('jquery.livequery.js')); ?>
	<?php $this->script(theme_resource('jquery.scrollTo-1.4.2.js')); ?>
	<?php $this->script(theme_resource('jquery.simpledialog.js')); ?>

	<?php $this->script(theme_resource('jquery.layout-latest.js')); ?>

	<?php $this->script(theme_resource('flexigrid/flexigrid.js')); ?>

	<?php $this->script(resource('jquery-treeview/jquery.treeview.js'))?>
	<?php $this->script(resource('jquery-treeview/jquery.treeview.async.js'))?>
	<?php $this->script(resource('jq-plugins/auto.complete.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.cookie.js')); ?>
	<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.jeditable.js')); ?>


	<?php $this->script(resource('general.js')); ?>
	
	<?php $this->script(theme_resource('relapse.js')); ?>
	<?php $this->script(theme_resource('relapse-projects.js')); ?>
	<?php $this->script(theme_resource('relapse-features.js')); ?>
	<?php $this->script(theme_resource('relapse-tasks.js')); ?>
	<?php $this->script(theme_resource('relapse-issues.js')); ?>
	<?php $this->script(theme_resource('relapse-clients.js')); ?>

	<script type="text/javascript">
	var CURRENT_USER_ID = '<?php echo za()->getUser()->getUsername();?>';
	var NOTES_URL = '<?php echo build_url('note', 'view')?>';
	var VALIDATION_TOKEN = '<?php echo $this->requestValidator(true)?>';
	var BASE_URL = '<?php echo build_url(); ?>';

	$(document).ready(function() {
		$('#ajax-loading').ajaxStart(function() {
			$(this).show();
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
   		<div id="search-box">
   		<?php $this->paneSearchBox() ?>
   		</div>
   		<?php endif; ?>

   		<div id="top-left-block">
   			
			<?php echo $this->loginOutBox(); ?>
   		</div>

		<div class="clear"></div>
	</div>

	<div id="content">
		<div id="ajax-loading" style="z-index: 2001; position: fixed; top: 15px; right: 15px; display: none;">
	    	<img src="<?php echo resource('images/ajax-loader.gif')?>" />
    	</div>

		<?php echo $this->childViewContent; ?>

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