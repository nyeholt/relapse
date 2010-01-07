<h2>
<?php $this->o($this->model->id ? 'Edit '.$this->model->getTitle() : 'New File');?>
</h2>
<form method="post" action="<?php echo build_url('file', 'upload');?>" enctype="multipart/form-data">
<input type="hidden" name="returnurl" value="<?php $this->o($this->returnUrl)?>" />
<?php if (isset($this->parent)): ?>
<input type="hidden" value="<?php echo $this->parent?>" name="parent" />
<?php endif;?>
<?php if ($this->model->id): ?>
<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
<?php if ($this->projectid): ?>
<input type="hidden" value="<?php echo $this->projectid?>" name="projectid" />
<?php endif; ?>
<?php if (mb_strlen($this->picker)): ?>
<input type="hidden" value="<?php echo $this->picker?>" name="picker" />	
<?php endif;?> 

<div class="inner-column">
    <p>
    <label for="file">File</label>
    <input class="input" type="file" name="file" id="file" />
    </p>
    <?php $this->textInput('Filename', 'filename') ?>
    <?php $this->textInput('Title', 'title') ?>
    <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
    <?php $this->yesNoInput('Is Private?', 'isprivate') ?>
    <?php endif; ?>
</div>
<div class="inner-column">
	<?php $this->textInput('Description', 'description', true) ?>
</div>
<p class="clear">
    <input type="submit" class="abutton" value="Save" accesskey="s" />
    <input type="button" class="abutton" onclick="history.go(-1);" value="Cancel" />
</p>
</form>