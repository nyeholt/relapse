<?php $deleteStyle = isset($this->existing) ? 'inline' : 'none' ?>
<?php $addStyle = $deleteStyle == 'inline' ? 'none' : 'inline' ?>

<p>
<a style="display: <?php echo $deleteStyle?>;" id="delete-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->itemid?>', type:'<?php echo $this->itemtype?>'}, function() {$('#delete-watch').hide();$('#add-watch').show(); }); return false; ">Remove Watch</a>
<a style="display: <?php echo $addStyle?>;" id="add-watch" href="#" onclick="$.get('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->itemid?>', type:'<?php echo $this->itemtype?>'}, function() {$('#add-watch').hide();$('#delete-watch').show(); }); return false;">Add Watch</a>
</p>
<?php foreach ($this->notes as $note): ?>

<div class="note">
	<div class="note-header">
    <h4><?php $this->o($note->title)?></h4>
    <p><em>By <?php $this->o($note->userid.' on '.$note->created)?></em></p>
    </div>
    <div class="note-body"><?php $this->bbCode($note->note);?></div>
</div>

<?php endforeach; ?>