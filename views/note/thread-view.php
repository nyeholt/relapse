<?php $deleteStyle = isset($this->existing) ? 'inline' : 'none' ?>
<?php $addStyle = $deleteStyle == 'inline' ? 'none' : 'inline' ?>

<div id="watch-controls">

</div>

<h2><?php $this->o($this->notes[0]->title);?></h2>

<?php $note = null; ?>
<?php foreach ($this->notes as $note): ?>

<div class="note">
    <div class="note-header">
        <form class="inline right" method="post" action="<?php echo build_url('note', 'delete')?>" onsubmit="return confirm('Are you sure?')">
            <input type="hidden" value="<?php echo $note->id?>" name="id" />
            <input title="Delete" type="image" src="<?php echo resource('images/delete.png')?>" />
        </form>
        <h4><?php $this->o($note->title)?></h4>
        <span class="note-by">By <?php $this->o($note->userid.' on '.$note->created)?></span>
    </div>
    <div class="note-body"><?php $this->bbCode($note->note);?></div>
</div>

<?php endforeach; ?>

<div class="note">
<form method="post" action="<?php echo build_url('note', 'add');?>">
    <input type="hidden" value="<?php echo $note->attachedtotype?>" name="attachedtotype"/>
    <input type="hidden" value="<?php echo $note->attachedtoid?>" name="attachedtoid"/>
    <input type="hidden" value="<?php echo za()->getUser()->getUsername()?>" name="userid"/>
    <p>
    <label for="note-title">Title:</label>
    <input class="input" type="text" name="title" id="note-title" value="Re: <?php $this->o(str_replace('Re: ', '', $note->title))?>" />
    </p>
    <p>
    <label for="note-note">Note:</label>
    <textarea name="note" rows="5" cols="45" id="note-note"></textarea>
    </p>
    <p>
        <input type="submit" class="abutton" value="Add Note" />
        <a class="abutton" style="display: <?php echo $deleteStyle?>;" id="delete-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->itemid?>', type:'<?php echo $this->itemtype?>'}, function() {$('#delete-watch').hide();$('#add-watch').show(); }); return false;">Remove Watch</a>
		<a class="abutton" style="display: <?php echo $addStyle?>;" id="add-watch" href="#" onclick="$.get('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->itemid?>', type:'<?php echo $this->itemtype?>'}, function() {$('#add-watch').hide();$('#delete-watch').show(); }); return false;">Add Watch</a>
    </p>
</form>
</div>