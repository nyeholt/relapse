<?php if ($this->model->id): ?>
<h2>Editing <?php $this->o($this->model->title); ?></h2>
<?php else: ?> 
<h2>Create new Client</h2>
<?php endif; ?>

<form method="post" action="<?php echo build_url('client', 'save');?>">
<?php if ($this->model->id): ?>
    <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
<div class="inner-column">
	<?php $this->textInput('Name', 'title') ?>
	<?php 
	if ($this->u()->hasRole(User::ROLE_USER)) {
	    $this->textInput('Description', 'description', true);
    }
	?>
    <?php $this->textInput('Website', 'website') ?>
    <?php $this->textInput('Postal Address', 'postaladdress') ?>
    <?php $this->textInput('Billing Address', 'billingaddress') ?>
    
</div>
<div class="inner-column">
    
    <?php $this->textInput('Email', 'email') ?>
    <?php $this->textInput('Phone', 'phone') ?>
    <?php $this->textInput('Fax', 'fax') ?>
  	<?php 
	if ($this->u()->hasRole(User::ROLE_USER)) {
	    $this->selectList('Relationship', 'relationship', $this->relationships);
    }?>
    
</div>
<p class="clear">
        <input type="submit" class="abutton" value="Save" accesskey="s" />
        <input type="button" class="abutton" onclick="history.go(-1);" value="Close" />
    </p>
</form>