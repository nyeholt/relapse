<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
<title><?php echo za()->getConfig('name');?> <?php if (isset($this->childView)) { echo " &mdash; "; $this->o($this->childView->title); } ?></title>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="<?php echo resource('new-style.css')?>"></link>
<link rel="stylesheet" type="text/css" href="<?php echo resource('jscalendar/calendar-system.css')?>"></link>
<link rel="stylesheet" href="<?php echo resource('tabs.css')?>" type="text/css" media="print, projection, screen"></link>
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="<?php echo resource('jq-plugins/jquery.tabs-ie.css')?>" type="text/css" media="print, projection, screen"></link>
<![endif]-->
<?php $this->style(resource('wymeditor/skins/default/screen.css')); ?>
<?php $this->script(resource('jquery-latest.pack.js')); ?>

<?php $this->script(resource('wymeditor/jquery.wymeditor.js')); ?>
<?php $this->script(resource('jscalendar/calendar_stripped.js')); ?>
<?php $this->script(resource('jscalendar/lang/calendar-en.js')); ?>
<?php $this->script(resource('jscalendar/calendar-setup.js')); ?>
<?php $this->script(resource('jq-plugins/auto.complete.js')); ?>
<?php $this->script(resource('jq-plugins/interface.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.cookie.js')); ?>
<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.history_remote.pack.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.tabs.pack.js')); ?>

<?php $this->script(resource('general.js')); ?>



<script type="text/javascript">

var CURRENT_USER_ID = '<?php echo za()->getUser()->getUsername();?>';

var NOTES_URL = '<?php echo build_url('note', 'view')?>';

// Shadows!
$(document).ready(
    function() {
        
        $('#ajax-loading').ajaxStart(function() {
            $(this).show();
        }).ajaxStop(function() {
            $(this).hide();
        });
        
		$('#right-column').mouseover(function() {
			$(this).css({opacity: '1', filter: 'alpha(opacity=100)'});
		});
		$('#right-column').mouseout(function() {
			$(this).css({opacity: '.30', filter: 'alpha(opacity=30)'});
		});

		$('#right-column').Draggable(
			{
				ghosting: false,
				onChange: function() {
					var left = $(this).css('left').replace(/px/, '');
					if (left < 0) {
						left = 10;
						$(this).css('left', left+"px");
					}
					
					
					$.cookie('action_list_x', left+"px", {path: '/'});
					$.cookie('action_list_y', $(this).css('top'), {path: '/'});
				}
			}
		);
		
		var newLeft = $.cookie('action_list_x');
		var newTop = $.cookie('action_list_y');
		
		if (newLeft != null) {
			$('#right-column').css('left', newLeft);
		}
		
		if (newTop != null) {
			$('#right-column').css('top', newTop);
		}
		
    }
);

</script>

</head>
<body>

    <div id="header">
    	<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
   		<div id="search-box">
   		<?php $this->searchBox() ?>
   		</div>
   		<?php endif; ?>
   		
   		<div id="session-info">
    		<?php echo $this->loginOutBox(); ?>
	   	</div>
   		
	    <div class="clear"></div>
	</div>

<div id="container">	
	<div id="menu">
    	<ul id="options">
    		<li><a href="<?php echo build_url('index'); ?>" title="Home">Home</a></li>
    	</ul>
    	<img id="ajax-loading" style="float: right; display: none;" src="<?php echo resource('images/ajax-loader.gif')?>" />
    		
    	<div style="clear: both"></div>
    </div>

	<div id="content-wrapper">
		<?php if (isset($this->childView->displayMenu)): ?>
		
			<div id="left-column">
				
				<ul id="crm-tree">
				
				</ul>
			</div>
		<div id="middle-column" style="margin-left: 300px">
		<?php else: ?>
		<div id="middle-column" class="panel">
		<?php endif;?>
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

		
		<?php if ($this->u()->hasRole(User::ROLE_USER) && isset($this->childView->actionList)): ?>
		
		<div id="right-column">
		
		 <?php echo $this->childView->actionList->toString(); $mainContentId = '';?>

		</div>
		<?php endif; ?>
		
		
	</div>
	<div class="clear"></div>
	
</div>

<div id="last-visited">
	<p>
	Last visited: <?php $this->o($this->u()->getLastLogin())?>
	</p>
</div>


<div id="info-container">
</div>

</body>
</html>