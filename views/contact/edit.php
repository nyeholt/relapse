<?php if ($this->model->id): ?>
    <div id="parent-links">
        <a title="Parent Client" href="<?php echo build_url('client', 'view', array('id'=>$this->client->id, '#contacts'));?>"><img src="<?php echo resource('images/client.png')?>"/></a>
    </div>
<?php endif; ?>

<h2><?php $this->o($this->model->id ? 'Edit contact '.$this->model->firstname.' '.$this->model->lastname : 'New Contact')?></h2>

<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
	<?php if (!$this->assocUser && $this->model->id): ?>
	<div id="contact-to-user">
	<form method="post" action="<?php echo build_url('contact', 'createuser');?>">
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<input type="submit" value="Create User" />
	</form>
	</div>	
	<?php elseif ($this->model->id): ?>
	<div id="contact-edit-user">
		<p>This contact has an extranet login. 
		<a href="<?php echo build_url('user', 'edit', array('id'=>$this->assocUser->id))?>">Click Here</a>
		 to change the user's password.</p>
	</div>
	<?php endif; ?>
	
<?php endif; ?>


<form method="post" action="<?php echo build_url('contact', 'save');?>">
<?php if ($this->model->id): ?>
    <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>

<div class="inner-column">
	<?php $this->textInput('First Name', 'firstname'); ?>
	<?php $this->textInput('Last Name', 'lastname'); ?>
	<?php $this->textInput('Title', 'title'); ?>
	<?php $this->textInput('Department', 'department'); ?>
	<?php $this->textInput('Email', 'email'); ?>
	<?php $this->textInput('Alternate Email', 'altemail'); ?>
	<?php $this->textInput('Mobile', 'mobile'); ?>
</div>
<div class="inner-column">
	<?php $this->textInput('Postal Address', 'postaladdress', true) ?>
	<?php $this->textInput('Business Address', 'businessaddress', true) ?>
    <?php $this->textInput('Direct Line', 'directline'); ?>
    <?php $this->textInput('Fax', 'fax'); ?>
    
    <?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
    <p>
    <label for="clientid">Client:</label>
    <select name="clientid" id="clientid">
        <?php 
        $sel = $this->model->clientid ? $this->model->clientid : $this->client->id;
        foreach ($this->clients as $client): ?>
            <option value="<?php echo $client->id?>" <?php echo $sel == $client->id ? 'selected="selected"' : '';?>><?php $this->o($client->title);?></option>
        <?php endforeach; ?>
    </select>
    </p>
	<?php else: ?>
        	<input type="hidden" name="clientid" value="<?php $this->o($this->model->clientid ? $this->model->clientid : $this->client->id)?>" />
    <?php endif; ?>    
</div>
<p class="clear">
    <input type="submit" class="abutton" value="Save" accesskey="s" />
    <?php if ($this->model->id): ?>
    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('client', 'view', array('id'=>$this->model->clientid, '#contacts')) ?>'" value="Close" />    
    <?php else: ?>
    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('client', 'view', array('id'=>$this->client->id, '#contacts')) ?>'" value="Close" />        
    <?php endif; ?>
    

    </p>
</form>