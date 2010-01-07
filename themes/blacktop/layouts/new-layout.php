<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	
<!-- ==========================================================	-->
<!--	Created by Devit Schizoper                          	-->
<!--	Created HomePages http://LoadFoo.starzonewebhost.com   	-->
<!--	Created Day 01.12.2006                              	-->
<!-- ========================================================== -->

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
   <meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="author" content="LoadFoO" />
	<meta name="description" content="Site description" />
	<meta name="keywords" content="key, words" />
	<title><?php echo za()->getConfig('name');?> <?php if (isset($this->childView)) { echo " &mdash; "; $this->o($this->childView->title); } ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo theme_resource('blacktop-style.css')?>"></link>
	<link rel="stylesheet" type="text/css" href="<?php echo resource('jscalendar/calendar-system.css')?>"></link>
	<link rel="stylesheet" type="text/css" href="<?php echo resource('time-table.css')?>"></link>

	<?php $this->style(resource('jquery-treeview/jquery.treeview.css'))?>
	
	<?php $this->script(resource('jquery-1.2.2-b.js')); ?>
	<?php $this->script(resource('time-table.js')); ?>
	
	<?php $this->script(resource('jquery-treeview/jquery.treeview.js'))?>
	<?php $this->script(resource('jquery-treeview/jquery.treeview.async.js'))?>

	<?php $this->script(resource('jscalendar/calendar_stripped.js')); ?>
	<?php $this->script(resource('jscalendar/lang/calendar-en.js')); ?>
	<?php $this->script(resource('jscalendar/calendar-setup.js')); ?>

	<?php $this->script(resource('jq-plugins/jquery.dimensions.min.js')); ?>
	<?php $this->script(resource('jq-plugins/auto.complete.js')); ?>
	<?php $this->script(resource('jq-plugins/interface.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.cookie.js')); ?>
	<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.history_remote.pack.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.jeditable.js')); ?>

	<?php $this->script(resource('general.js')); ?>
	<?php $this->script(theme_resource('general.js')); ?>

	<style type="text/css">
	body {
		background: url(<?php echo theme_resource('images/top_bg.gif') ?>);
		background-repeat: repeat-x;
	}

	#top h2 {
		background: url(<?php echo theme_resource('images/bg_t.gif') ?>) no-repeat;
	}
	#menu li a:hover {
		background: url(<?php echo theme_resource('images/bg_menu.gif') ?>);
	}
	
	#menu li a.current {
		background: url(<?php echo theme_resource('images/bg_menu.gif') ?>);
	}
	
	#nav a:hover{
		background: url(<?php echo theme_resource('images/bg_t.gif') ?>) no-repeat;
		background-color: #fff;
	}	
	
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
	        $('#quick-task-form').submit(function() {
				$.post('<?php echo build_url('task', 'quickcreate')?>', {title: $('#quick-task').val()}, function (data) {
					popup('<?php echo build_url('timesheet', 'record')?>id/'+data, 'timer', '500', '300');
				});
				return false;
			});
	});
	</script>
</head>

<body>
<div id="wrap">
	<div id="top">
		<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
   		<div id="search-box">
   		<?php $this->searchBox() ?>
   		</div>
   		<?php endif; ?>
   		<div id="top-left-block" style="width: 600px;">
   			<div id="session-info" style="float: right;">
	    		<?php echo $this->loginOutBox(); ?>
		   	</div>
   			<img style="padding: 1em 0em 0em 1em;" src="<?php echo resource('images/logo.gif')?>"></img>
   		</div>
	</div>
	<div id="top-menu">
		<ul id="options">
    		<li><a href="<?php echo build_url('index');?>">Home</a></li>
    		<?php if (za()->getUser()->hasRole(User::ROLE_EXTERNAL)): ?>
    		<li><a href="<?php echo build_url('issue', 'edit') ?>">Create Request</a></li>
    		<?php endif; ?>
    		<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
    		<li><a href="<?php echo build_url('task', 'list') ?>">My Tasks</a></li>
			<li><a href="<?php echo build_url('issue', 'index', array('mineOnly' => 1)); ?>">My Requests</a></li>
    		<?php endif; ?>
    		<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
<!-- 		<li><a href="<?php echo build_url('issue', 'edit') ?>">Generate Project Reports</a></li>
			<li><a href="<?php echo build_url('issue', 'edit') ?>">Generate Timesheets</a></li> -->    
    		<?php endif; ?>
    	</ul>
    	<?php if (za()->getUser()->hasRole(User::ROLE_USER) && false) : ?>
    	<form id="quick-task-form" style="float: right">
    		<?php $this->requestValidator() ?>
    		<input type="text" id="quick-task" size="40" accesskey="a" /> <input type="submit" value="Create Task" />
    	</form>
    	<?php endif; ?>
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

		<div id="right">
			<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
			<div class="box">
				<h2>View ... </h2>
				<ul>
					
					<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
		    		<li><a href="<?php echo build_url('client'); ?>" title="Clients">All Clients</a></li>
		    		<li><a href="<?php echo build_url('project'); ?>" title="Projects">All Projects</a></li>
		    		<li><a href="<?php echo build_url('contact');?>" title="Contacts">All Contacts</a></li>
		    		<?php $this->getMods($this, 'main-menu'); ?>
		    		<?php endif; ?>

				</ul>
			</div>
			<div class="box">
				<h2>Timesheets ... </h2>
				<ul>
					<!--<li><a href="<?php echo build_url('timesheet', 'index'); ?>">Weekly Timesheets</a></li>-->
					<li><a href="<?php echo build_url('timesheet', 'filter'); ?>">Detailed Report</a></li>
					<!--<li><a href="<?php echo build_url('timesheet', 'summary'); ?>">Timesheet Summary</a></li>-->
				</ul>
			</div>
				<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
				<div class="box">
					<h2>Admin ... </h2>
					<ul>
					<li><a href="<?php echo build_url('timesheet', 'filterSummary'); ?>">Summary Report</a></li>
						<li><a href="<?php echo build_url('leave', 'list'); ?>">Leave</a></li>
						<li><a href="<?php echo build_url('index','index',null, false, 'expenses');?>">Expenses</a></li>
			    		<li><a href="<?php echo build_url('event');?>">Events</a></li>
			    		<li><a href="<?php echo build_url('mailout');?>">Mailouts</a></li>
						<li><a href="<?php echo build_url('admin');?>">Helpdesk Admin</a></li>					
					</ul>
				</div>
	            <?php endif; ?>
			<?php endif; ?>
			<?php echo isset($this->childView->actionList) ? $this->childView->actionList->toString() : '' ?>

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
