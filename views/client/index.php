<h2>
Clients
<a href="<?php echo build_url('client', 'edit')?>" class="abutton">Add</a>
</h2>

<div class="inner-column">
<h3>Name</h3>
<ul>
<?php foreach ($this->clients as $client): ?>
    <li>
    <a href="#" onclick="displayItem('client', '<?php echo $client->id?>'); return false;"><?php $this->o($client->name);?></a>
    <a title="Delete Client" onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('client', 'delete', array('id' => $client->id))?>'; return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
    <div class="hidden-info" id="client-info-<?php echo $client->id?>">
        <div>
            <div class="info-block">
                <h3>
                <a style="float: right;" href="<?php echo build_url('contact', 'edit', array('clientid' => $client->id))?>"><img src="<?php echo resource('images/add.png')?>" /></a>
                <a href="#" onclick="loadClientData(<?php echo $client->id?>, 'contacts','<?php echo build_url('contact', 'contactlist'); ?>'); return false;">Contacts</a>
                </h3>
                <div id="client-info-<?php echo $client->id?>-contacts"></div>
            </div>
            
            <div class="info-block">
                <h3>
                <a style="float: right;" href="<?php echo build_url('project', 'edit', array('clientid' => $client->id))?>"><img src="<?php echo resource('images/add.png')?>" /></a>
                <a href="#" onclick="loadClientData(<?php echo $client->id?>, 'projects', '<?php echo build_url('project', 'list'); ?>'); return false;">Projects</a>
                </h3>
                <div id="client-info-<?php echo $client->id?>-projects"></div>
            </div>
            
            <div class="info-block">
                <!--<input style="float: right;" type="button" onclick="location.href='<?php echo build_url('client', 'edit', array('id'=>$client->id))?>';" value="Edit" />-->
                
                <h3>
                <a style="float: right;" href="<?php echo build_url('client', 'edit', array('id' => $client->id))?>"><img src="<?php echo resource('images/pencil.png');?>" /></a>
                <?php $this->addNote($client->name, $client->id, 'client');?>
                <?php $this->viewNotes($client->id, 'client');?>
                Details
                </h3>
            
                <div class="micro-column gainlayout">
                <p>
                    <strong>Name</strong><br/>
                    <?php $this->o($client->name) ?>
                </p>
                <p>
                    <strong>Description</strong><br/>
                    <?php $this->o($client->description, true) ?>
                </p>
                <p>
                    <strong>Website</strong><br/>
                    <a href="<?php echo $client->website?>"><?php echo $client->website?></a>
                </p>
                <p>
                    <strong>Email</strong><br/>
                    <a href="mailto:<?php echo $client->email?>"><?php echo $client->email?></a>
                </p>
                </div>
                <div class="micro-column gainlayout">
                <p>
                    <strong>Postal Address</strong><br/>
                    <?php $this->o($client->postaladdress, true) ?>
                </p>
                <p>
                    <strong>Billing Address</strong><br/>
                    <?php $this->o($client->billingaddress, true) ?>
                </p>
                
                <p>
                    <strong>Phone</strong><br/>
                    <?php $this->o($client->phone) ?>
                </p>
                <p>
                    <strong>Fax</strong><br/>
                    <?php $this->o($client->fax) ?>
                </p>
                <p>
                    <strong>Relationship</strong><br/>
                    <?php $this->o($client->relationship) ?>
                </p>
                
                </div>
            <!--/details section-->
            </div>
        </div>
        
    <!-- end client info div -->
    </div>
    </li>
<?php endforeach; ?>
</ul>
</div>

<div class="inner-column" id="display-target">
    
</div>