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
	<!-- tabs CSS file -->
	<link rel="stylesheet" href="<?php echo resource('tabs.css')?>" type="text/css" media="print, projection, screen"></link>
	<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
	<!--[if lte IE 7]>
	<link rel="stylesheet" href="<?php echo resource('jq-plugins/jquery.tabs-ie.css')?>" type="text/css" media="print, projection, screen"></link>
	<![endif]-->
	
	<?php $this->script(resource('jquery-1.2.2-b.js')); ?>
	
	<?php $this->script(resource('jscalendar/calendar_stripped.js')); ?>
	<?php $this->script(resource('jscalendar/lang/calendar-en.js')); ?>
	<?php $this->script(resource('jscalendar/calendar-setup.js')); ?>

	<?php $this->script(resource('jq-plugins/jquery.dimensions.min.js')); ?>
	<?php $this->script(resource('jq-plugins/auto.complete.js')); ?>
	<?php $this->script(resource('jq-plugins/interface.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.cookie.js')); ?>
	<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.history_remote.pack.js')); ?>
	<?php $this->script(resource('jq-plugins/jquery.tabs.min.js')); ?>

	<?php $this->script(resource('general.js')); ?>
	

	
	<style type="text/css">
	body {
		background: url(<?php echo theme_resource('images/top_bg.gif') ?>);
		background-repeat: repeat-x;
	}
	ul {
		list-style-image: url(<?php echo theme_resource('images/bullet.gif') ?>);
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
	
	$(document).ready(
	    function() {
	        $('#ajax-loading').ajaxStart(function() {
	            $(this).show();
	            $(this).css('top', 15 + $().scrollTop());
	        }).ajaxStop(function() {
	            $(this).hide();
	        });
	    }
	);
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
   		
   		<div id="session-info">
    		<?php echo $this->loginOutBox(); ?>
	   	</div>
	</div>
	<div id="top-menu">
		<ul id="options">
    		<li><a href="<?php echo build_url('index'); ?>" title="Home">Home</a></li>
    		<li><a href="<?php echo build_url('file'); ?>" title="Files">Files</a></li>
    	</ul>
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
			
		</div>
	
	</div>
	<div id="footer">
	<div id="last-visited">
	<p>
	Last visited: <?php $this->o($this->u()->getLastLogin())?>
	</p>
</div>
	</div>
</div>

</body>
</html>