<h2>
<?php $this->o($this->model->id ? 'Edit '.$this->model->title : 'New Feature');?>
</h2>
<form method="post" action="<?php echo build_url('faq', 'save');?>">

<?php if ($this->model->id): ?>
<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>

	<?php $this->textInput('Title', 'title'); ?>
    <?php $this->textInput('Summary', 'description', true, 'rows="5" cols="40"'); ?>
	<?php $this->selectList('Author', 'author', $this->authors, $this->u()->getUsername(), 'username', 'username');?>
	<?php $this->wymInput('Content', 'faqcontent', array('stylesheet'=>"'".resource('style.css')."'")); ?>
	<?php $this->autoComplete('Tags', 'tags', build_url('tag', 'suggest'), 'size="60"'); ?>
	
<p class="clear">
    <input type="submit" class="button wymupdate" value="Save" accesskey="s" />
<?php if ($this->model->id): ?>
    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('faq', 'view', array('id'=>$this->model->id))?>'" value="Cancel" />
<?php else: ?>
    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('faq')?>'" value="Cancel" />
<?php endif; ?>

</p>
</form>