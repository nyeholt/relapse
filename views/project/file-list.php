
<div>
<ul id="file-listing">
<?php if (mb_strlen($this->parentPath)): ?>
	<li>
		<a href="<?php echo build_url('project', 'view', array('id' => $this->project->id, 'folder' => base64_encode($this->parentPath), "#files"))?>">Parent Directory</a>
	</li>
<?php endif; ?>
<?php foreach ($this->files as $file):?>
	<li>
	<?php if (is_string($file)): ?> 
		<a href="<?php echo build_url('project', 'view', array('id' => $this->project->id, 'folder' => base64_encode($this->base.$file), "#files"))?>"><?php $this->o($file)?></a>

	<?php else: ?> 
	<a class="action-icon" title="Edit file" href="<?php echo build_url('file', 'edit', array('id'=>$file->id, 'projectid'=>$this->project->id, 'returnurl'=>base64_encode('project/view/id/'.$this->project->id.'/#files'), 'parent'=>base64_encode($file->path)))?>">
		<img src="<?php echo resource('images/pencil.png')?>" />
	</a> 
	
	<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	<a class="action-icon" title="Delete file" href="<?php echo build_url('file', 'delete', array('id'=>$file->id, 'returnurl'=>base64_encode('project/view/id/'.$this->project->id.'/#files')))?>">
		<img src="<?php echo resource('images/delete.png')?>" />
	</a> 
	<?php endif; ?>

	<!-- the external filecontroller will need to check the project that a user wants to view
	this file within the context of. -->
	<a href="<?php echo build_url('file', 'view', array('id' => $file->id, 'projectid'=>$this->project->id)).htmlentities($file->filename)?>"><?php $this->o($file->getTitle())?></a>

	<?php if (!empty($file->description)): ?>
		<p><?php $this->o($file->description) ?></p>
	<?php endif; ?> 
	<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
</div>

<form method="post" action="<?php echo build_url('file', 'createfolder')?>">
	<p>
	
	<input type="hidden" value="<?php echo base64_encode($this->base)?>" name="parent" />
	<input type="text" value="" name="child" />
	<input type="submit" value="Create Folder" class="abutton" />
	</p>
	<p>
	</p>
</form>

	
<form method="post" action="<?php echo build_url('file', 'upload');?>" enctype="multipart/form-data">
	<input type="hidden" name="returnurl" 
	value="<?php echo base64_encode('project/view/id/'.$this->project->id.'/#files')?>" />
	<input type="hidden" value="<?php echo base64_encode($this->base)?>" name="parent" />
	<input type="hidden" value="<?php echo $this->project->id?>" name="projectid" />
	
	<div class="inner-column">
	<p><label for="file">File</label>
	<input class="input" type="file" name="file" id="file" /></p>
	<p><label for="filename">Filename:</label>
	<input class="input" type="text" name="filename" id="filename" /></p>
	<p><label for="title">Title:</label>
	<input class="input" type="text" name="title" id="title" /></p>
	
	</div>
	<div class="inner-column">
	<p><label for="description">Description:</label>
	<textarea class="input" name="description" id="description"></textarea>
	</p>
	</div>
	<p class="clear"><input type="submit" class="abutton" value="Upload"
		accesskey="a" /></p>
</form>
