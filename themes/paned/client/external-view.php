<h2><?php $this->o($this->client->title)?></h2>

<div id="client-container">
    <ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <li><a href="#projects"><span id="projects-index">Projects</span></a></li>
        <li><a href="#contacts"><span id="contacts-index">Contacts</span></a></li>
    </ul>
    <div id="details">
        
        <div class="micro-column">
            <p>
                <strong>Name</strong><br/>
                <?php $this->o($this->client->title) ?>
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
        </div>
        
        <p style="clear: left;">

        </p>
        
        <div id="client-info-<?php echo $this->client->id?>-issue">
		    <?php $this->dispatch('issue', 'clientlist', array('clientid'=>$this->client->id)); ?>
		    </div>
		    
		    <p>
			<a class="abutton" href="<?php echo build_url('issue', 'edit', array('clientid'=>$this->client->id))?>">Create Request</a>
	    </p>
        
    </div>
    
    <div id="projects">
        <div id="client-info-<?php echo $this->client->id?>-projects">
            <?php $this->dispatch('project', 'list', array('clientid'=> $this->client->id)); ?>
        </div>
    </div>
    
    <div id="contacts">
        <div id="client-info-<?php echo $this->client->id?>-contacts">
            <?php $this->dispatch('contact', 'contactlist', array('clientid'=> $this->client->id)); ?>
        </div>
        <p>
        <a class="abutton" href="<?php echo build_url('contact', 'edit', array('clientid' => $this->client->id))?>">Add Contact</a>
        </p>
    </div>
    
	    

</div>

<script type="text/javascript">
    $().ready(function(){
        $("#client-container").tabs();
    });
</script>