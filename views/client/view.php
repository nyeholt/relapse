<h2><?php $this->o($this->client->title)?></h2>

<div id="client-container">
    <ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <li><a href="#timesheet"><span>Timesheet</span></a></li>
        <li><a href="#contacts"><span id="contacts-index">Contacts</span></a></li>
        <li><a href="#projects"><span id="projects-index">Projects</span></a></li>
        <?php $this->getMods($this, 'client-view-index');?>
    </ul>
    <div id="details">
        
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
    
    <div id="timesheet">
    	<?php $this->dispatch('timesheet', 'list', array('clientid'=> $this->client->id)); ?>
    	<p>
            <a class="abutton" href="<?php echo build_url('timesheet', 'edit', array('clientid'=>$this->client->id))?>">Add Timesheet</a>
            <a class="abutton" href="<?php echo build_url('timesheet', 'index', array('clientid'=>$this->client->id))?>">View Current Times</a>
        </p>
    </div>
    
    <div id="contacts">
        <div id="client-info-<?php echo $this->client->id?>-contacts">
            <?php $this->dispatch('contact', 'contactlist', array('clientid'=> $this->client->id)); ?>
        </div>
        <p>
        <a class="abutton" href="<?php echo build_url('contact', 'edit', array('clientid' => $this->client->id))?>">Add Contact</a>
        </p>
    </div>
    
    <div id="projects">
        <div id="client-info-<?php echo $this->client->id?>-projects">
            <?php $this->dispatch('project', 'list', array('clientid'=> $this->client->id)); ?>
        </div>
        <p>
       	<a class="abutton" href="<?php echo build_url('project', 'edit', array('clientid' => $this->client->id))?>">Add Project</a>
        </p>
    </div>

<?php $this->getMods($this, 'client-view');?>

</div>

<script type="text/javascript">
    $().ready(function(){
        $("#client-container").tabs();
    });
</script>