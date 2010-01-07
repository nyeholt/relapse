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
<?php $this->script(resource('jq-plugins/formAjax.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.history_remote.pack.js')); ?>
<?php $this->script(resource('jq-plugins/jquery.tabs.min.js')); ?>

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
    }
);

</script>

</head>
<body>
<div id="container">
    <div id="header">
	    <div id="menu">
	    	<ul id="options">
	    		<li><a href="<?php echo build_url('client'); ?>" title="Your Company">Home</a></li>
	    	</ul>
	    	<div style="clear: both"></div>
	    </div>
    </div>
    <div id="session-info">
           <img id="ajax-loading" style="float: right; display: none;" src="<?php echo resource('images/indicator_circle.gif')?>" />
   		<?php echo $this->loginOutBox(); ?>
   	</div>

	<div id="content-wrapper">
		<div id="flash">
		<?php $this->showFlash($this->childView); ?>
		</div>
		<div id="errors">
		<?php $this->errors($this->childView); ?>
		</div>
		
		<div >
		<?php echo $this->childView->toString(); ?>
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