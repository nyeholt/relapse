
<div class="dataDetails" >
	<div class="inner-column">
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
	
	<div class="inner-column">
		<ul class="largeList">
			<li><?php $this->addToPane(build_url('project', 'list', array('clientid' => $this->client->id)), 'Projects', 'Projects for '.$this->client->title) ?></li>
			<li><?php $this->addToPane(build_url('project', 'edit', array('clientid' => $this->client->id)), 'Add Project', 'Add project to '.$this->client->title, 'RightPane') ?></li>
			<li><?php $this->addToPane(build_url('issue', 'list', array('clientid' => $this->client->id)), 'Issues', 'Issues for '.$this->client->title) ?></li>
			<li><?php $this->dialogPopin('currenttimes', "Current Timesheet", build_url('timesheet', 'index', array('clientid'=>$this->client->id)), array('width' => 1000)) ?></li>
			<li><?php $this->addToPane(build_url('contact', 'list', array('clientid'=>$this->client->id)), 'Contact List', 'Contacts for '.$this->client->title) ?></li>
			<li><?php $this->addToPane(build_url('client', 'edit', array('id'=>$this->client->id)), 'Edit this client', 'Edit '.$this->client->title, 'RightPane') ?></li>
		</ul>
	</div>
</div>
<div id="timesheet" class="std">
	<?php $this->dispatch('timesheet', 'list', array('clientid'=> $this->client->id)); ?>
	<p>
		<a class="abutton" href="<?php echo build_url('timesheet', 'edit', array('clientid'=>$this->client->id))?>">Add Timesheet</a>
		<a class="abutton" href="<?php echo build_url('timesheet', 'index', array('clientid'=>$this->client->id))?>">View Current Times</a>
	</p>
</div>