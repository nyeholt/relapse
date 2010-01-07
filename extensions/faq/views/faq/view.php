<h2>
<span style="float: right">
<?php if ($this->model->nextversionid == 0): ?>
[ <a href="<?php echo build_url('faq', 'edit', array('id'=>$this->model->id))?>">Edit this FAQ</a> ]
<?php endif; ?>
[ <a href="<?php echo build_url('faq');?>">List</a> ]
</span>
<?php $this->o($this->model->title)?>
</h2>

<p class="faq-summary">
<?php $this->o($this->model->description) ?>
</p>

<div class="faq-content">
<?php echo $this->model->faqcontent; ?>
</div>

<div class="faq-versions">
<fieldset>
<legend>Versions</legend>
<!-- list all versions -->
<ul>
<?php foreach ($this->versions as $version): ?>
<?php $class = $version->id == $this->model->id ? 'current-version' : 'version'; ?>
<li class="<?php echo $class?>"><a href="<?php echo build_url('faq', 'view', array('id'=>$version->id))?>"><?php $this->o($version->title)?></a><?php $this->o(' - Created '.$version->created)?></li>
<?php endforeach; ?>
</ul>
</fieldset>
</div>