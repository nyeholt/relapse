<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
<title><?php echo za()->getConfig('name');?> <?php if (isset($this->childView)) { echo " &mdash; "; $this->o($this->childView->title); } ?></title>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="<?php echo resource('style.css')?>"></link>
<link rel="stylesheet" type="text/css" href="<?php echo resource('jscalendar/calendar-system.css')?>"></link>
<link rel="stylesheet" href="<?php echo resource('jq-plugins/jquery.tabs.css')?>" type="text/css" media="print, projection, screen"></link>
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
<?php $this->script(resource('jq-plugins/corners.js')); ?>
<?php $this->script(resource('jq-plugins/interface.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.cookie.js')); ?>
<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.history_remote.pack.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.tabs.pack.js')); ?>

<?php $this->script(resource('general.js')); ?>

<style type="text/css">
    
    .wrap0, .wrap1, .wrap2, .wrap3 {
        display:inline-table;
    /* \*/display:block;/**/
    }
    .wrap0 {
        float:left;
        background:url(<?php echo resource('images/shadow.gif')?>) right bottom no-repeat;
    }
    .wrap1 {
        background:url(<?php echo resource('images/shadow180.gif')?>) no-repeat;
    }
    .wrap2 {
        background:url(<?php echo resource('images/corner_bl.gif')?>) -18px 100% no-repeat;
    }
    .wrap3 {
        padding:10px 14px 14px 10px;
        background:url(<?php echo resource('images/corner_tr.gif')?>) 100% -18px no-repeat;
    }
    
</style>

<script type="text/javascript">

var CURRENT_USER_ID = '<?php echo za()->getUser()->getUsername();?>';

var NOTES_URL = '<?php echo build_url('note', 'view')?>';

// Shadows!
$(document).ready(
    function() {
        $("#action-list").wrap("<div class='wrap0'><div class='wrap1'><div class='wrap2'>" +
"<div class='wrap3'></div></div></div></div>");

        $('#ajax-loading').ajaxStart(function() {
            $(this).show();
        }).ajaxStop(function() {
            $(this).hide();
        });
        
        var addNoteForm = $('#add-note-form');
        addNoteForm.ajaxForm(function() { 
            $('#add-note').hide()
        });
        
        $('#add-note').Draggable(
			{
				ghosting:	true,
				handle: '.drag-handle',
				opacity:	0.5
			}
		);
		
		$('#notes-container').Draggable(
			{
				ghosting:	true,
				handle: '.drag-handle',
				opacity:	0.5
			}
		);

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
<div id="container">
    <div id="header">
    	<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
   		<div id="search-box">
   		<?php $this->searchBox() ?>
   		</div>
   		<?php endif; ?>
   		
    	<div id="menu">
	    	<ul id="options">
	    		<?php if (za()->getUser()->getRole() == User::ROLE_EXTERNAL): ?>
	    		<li>
				    <a href="<?php echo build_url('external');?>">Home</a>
				</li>
	    		<?php endif; ?>
	    		<?php if (za()->getUser()->hasRole(User::ROLE_USER)): ?>
	    		<li><a href="<?php echo build_url(); ?>" title="Return home">Home</a></li>
	    		<li><a href="<?php echo build_url('client'); ?>" title="Clients">Clients</a></li>
	    		<li><a href="<?php echo build_url('project'); ?>" title="Projects">Projects</a></li>
	    		<li><a href="<?php echo build_url('contact');?>" title="Contacts">Contacts</a></li>
	    		<?php $this->getMods($this, 'main-menu'); ?>
	    		<?php endif; ?>
				
	    		<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
				<li>
				    <a href="<?php echo build_url('admin');?>">Admin</a>
				</li>
	            <?php endif; ?>
	    	</ul>
	    </div>
	</div>

    <div id="session-info">
            <img id="ajax-loading" style="float: right; display: none;" src="<?php echo resource('images/ajax-loader.gif')?>" />
    		<?php echo $this->loginOutBox(); ?>
    		
    	</div>
	<div id="content-wrapper">
		<div id="flash">
		<?php $this->showFlash($this->childView); ?>
		</div>
		<div id="errors">
		<?php $this->errors($this->childView); ?>
		</div>
		
		<?php if ($this->u()->hasRole(User::ROLE_USER) && isset($this->childView->actionList)): ?>
		
		<div id="right-column">
		
		 <?php echo $this->childView->actionList->toString(); $mainContentId = '';?>

		</div>
		<?php endif; ?>
		
		<div id="<?php echo isset($mainContentId) ? $mainContentId : 'maent'?>">
		<?php echo $this->childViewContent; ?>
		</div>
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

<!-- The divs for adding and displaying notes -->
<div id="add-note">
    <div class="drag-handle"></div>
    <form id="add-note-form" method="post" action="<?php echo build_url('note', 'add');?>">
        <input type="hidden" value="" name="attachedtotype" id="attachedtotype" />
        <input type="hidden" value="" name="attachedtoid" id="attachedtoid" />
        <input type="hidden" value="" name="userid" id="noteuserid"/>
        <input type="hidden" value="" name="subject" id="notesubject" />
        
        <label for="add-note-title">Title:</label>
        <input class="input" type="text" name="title" id="add-note-title" />
        <br/>
        <label for="add-note-note">Note:</label>
        <textarea name="note" rows="5" cols="25" id="add-note-note"></textarea>
        
        <input type="button" value="Add" onclick="this.disabled=true; $('#add-note-form').submit();" id="add-note-button"/>
        <input type="button" onclick="$('#add-note').hide()" value="Close" />
    </form>
</div>

<div id="notes-container">
	<div class="drag-handle"></div>
    <div id="notes-content"></div>
    <input style="float:right;" type="button" onclick="$('#notes-container').hide()" value="Close" />
    
</div>
</body>
</html>