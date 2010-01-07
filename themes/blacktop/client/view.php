<h2><?php $this->o($this->client->title)?></h2>

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
<?php endif; ?>
<div class="project-tree" id="project-<?php echo $this->client->id?>-tree" style="display: none;">
</div>

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
        
        <div id="issues">
        	<br/>
		    <?php $this->dispatch('issue', 'clientlist', array('clientid'=>$this->client->id)); ?>
		    <p>
			<a class="abutton" href="<?php echo build_url('issue', 'edit', array('clientid'=>$this->client->id))?>">Create Request</a>
		    </p>
		</div>
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