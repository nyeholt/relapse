<div class="std">
	<?php
	// info for whether to display the 'add to favourites' stuff
	$deleteStyle = isset($this->existingWatch) && $this->existingWatch ? 'inline' : 'none';
	$addStyle = $deleteStyle == 'inline' ? 'none' : 'inline';
	?>

	<?php if ($this->client->id): ?>
	<div id="parent-links">
		<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
		<a title="Expand" href="#" onclick="return displayProjectTree(this, <?php echo $this->client->id?>, 'Client', '<?php echo build_url('tree', 'view')?>');"><img src="<?php echo resource('images/tree.png')?>"/></a>

		<a title="Unsubscribe" style="display: <?php echo $deleteStyle?>;" id="delete-client-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->client->id?>', type:'<?php echo get_class($this->client)?>'}, function() {$('#delete-client-watch').hide();$('#add-client-watch').show(); }); return false; "><img src="<?php echo resource('images/thumb_down.png')?>"/></a>
		<a title="Subscribe" style="display: <?php echo $addStyle?>;" id="add-client-watch" href="#" onclick="$.get('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->client->id?>', type:'<?php echo get_class($this->client)?>'}, function() {$('#add-client-watch').hide();$('#delete-client-watch').show(); }); return false; "><img src="<?php echo resource('images/thumb_up.png')?>"/> </a>

		<?php endif; ?>
	</div>
	<div class="project-tree" id="project-<?php echo $this->client->id?>-tree" style="display: none;">
	</div>

	<?php $this->o($this->client->title.' (#'.$this->client->id.')')?>
	<?php endif; ?>
	
</div>


<div id="projects" class="std">
	<div id="client-info-<?php echo $this->client->id?>-projects">
		<?php $this->dispatch('project', 'list', array('clientid'=> $this->client->id)); ?>
	</div>
	<p>
	<a class="abutton" href="<?php echo build_url('project', 'edit', array('clientid' => $this->client->id))?>">Add Project</a>
	</p>
</div>

<?php include dirname(__FILE__).'/../issue/issue-list.php'; ?>

<div id="timesheet" class="std">
	<?php $this->dispatch('timesheet', 'list', array('clientid'=> $this->client->id)); ?>
	<p>
		<a class="abutton" href="<?php echo build_url('timesheet', 'edit', array('clientid'=>$this->client->id))?>">Add Timesheet</a>
		<a class="abutton" href="<?php echo build_url('timesheet', 'index', array('clientid'=>$this->client->id))?>">View Current Times</a>
	</p>
</div>

<div id="contacts" class="std">
	<div id="client-info-<?php echo $this->client->id?>-contacts">
		<?php $this->dispatch('contact', 'contactlist', array('clientid'=> $this->client->id)); ?>
	</div>
	<p>
	<a class="abutton" href="<?php echo build_url('contact', 'edit', array('clientid' => $this->client->id))?>">Add Contact</a>
	</p>
</div>

<div class="std dataDetails" >
	<div class="micro-column">
		<p>
			<strong>Name</strong><br/>
			<?php $this->o($this->client->title) ?>
		</p>
		<p>
			<strong>Description</strong><br/>
			<?php $this->o($this->client->description, true) ?>
		</p>
		<p>
			<strong>Website</strong><br/>
			<a href="<?php echo $this->client->website?>" target="_blank"><?php echo substr($this->client->website, 0, 16).'...'?></a>
		</p>
		<p>
			<strong>Email</strong><br/>
			<a href="mailto:<?php echo $this->client->email?>"><?php echo $this->client->email?></a>
		</p>
	</div>
	
	<div class="micro-column">
		<p>
			<strong>Postal Address</strong><br/>
			<?php $this->o($this->client->postaladdress, true) ?>
		</p>
		<p>
			<strong>Billing Address</strong><br/>
			<?php $this->o($this->client->billingaddress, true) ?>
		</p>

		<p>
			<strong>Phone</strong><br/>
			<?php $this->o($this->client->phone) ?>
		</p>
		<p>
			<strong>Fax</strong><br/>
			<?php $this->o($this->client->fax) ?>
		</p>
		<p>
			<strong>Relationship</strong><br/>
			<?php $this->o($this->client->relationship) ?>
		</p>

	</div>

	<p style="clear: left;">
	<a class="abutton" href="<?php echo build_url('client', 'edit', array('id' => $this->client->id))?>">Edit This Client</a>
	</p>
</div>
