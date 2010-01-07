<?php if (!$this->project->svnurl): ?>
	<h2>Project SVN URL not set</h2>
	<p>
	You must specify the URL of the SVN project (including the /trunk part) before
	you can create code reviews
	</p>
<?php else: ?>

	<?php if ($this->model->id): ?>
	<div id="parent-links">
	    <?php if (isset($this->project)): ?>
	        <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->project->id, '#codereviews'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
	    <?php endif;?>
	</div>
	<?php endif; ?>
	
	<h2>
	<?php $this->o($this->model->id ? 'Edit '.$this->model->title : 'New Code Review');?>
	</h2>
	<form method="post" action="<?php echo build_url('codereview', 'save');?>">
	
	<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
	
	<?php if ($this->model->id): ?>
	<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
	<?php endif; ?>
	<div class="inner-column">
		<?php $this->textInput('Title', 'title'); ?>
       	<?php $this->selectList('Status', 'status', $this->statuses) ?>

		<?php $this->textInput('Revision Number', 'revision'); ?>
	
		<?php $this->textInput('From Revision', 'previousrevision'); ?>
	    <?php $this->textInput('Description', 'description', true, 'rows="5" cols="40"'); ?>
		<?php // $this->selectList('Created By', 'author', $this->authors, $this->u()->getUsername(), 'username', 'username');?>
	</div>
	<div class="inner-column">
		<?php $this->yesNoInput('Clear data', 'cleardata'); ?>
	</div>
	<p class="clear">
	    <input type="submit" class="abutton" value="Save" accesskey="s" />
	<?php if ($this->model->id): ?>
	<?php else: ?>
	    
	<?php endif; ?>
	
	</p>
	</form>
	
	<script type="text/javascript">
	$().ready(function() {
		$('#revision').change(function(data) {
			var curr = $(this).val();
			if (curr > 0) {
				$('#previousrevision').val(curr - 1);
			}
	
		});
	});
	</script>
<?php endif; ?>