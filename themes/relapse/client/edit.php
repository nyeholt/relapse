<form method="post" action="<?php echo build_url('client', 'save');?>" class="data-form ajaxForm">
<?php if ($this->model->id): ?>
    <input type="hidden" value="<?php echo $this->model->id?>" name="id" />
<?php endif; ?>
	<?php if ($this->viaajax): ?>
	<input type="hidden" value="1" name="_ajax" />
	<?php endif; ?>
	<fieldset>
	<?php $this->textInput('Name', 'title') ?>
	<?php 
	if ($this->u()->hasRole(User::ROLE_USER)) {
	    $this->textInput('Description', 'description', true);
    }
	?>
	<?php
	if ($this->u()->hasRole(User::ROLE_USER)) {
	    $this->selectList('Relationship', 'relationship', $this->relationships);
    }?>
    <?php $this->textInput('Website', 'website') ?>
    </fieldset>

	<fieldset>
    <?php $this->textInput('Email', 'email') ?>
    <?php $this->textInput('Phone', 'phone') ?>
    <?php $this->textInput('Fax', 'fax') ?>
	<?php $this->textInput('Postal Address', 'postaladdress') ?>
    <?php $this->textInput('Billing Address', 'billingaddress') ?>
	</fieldset>

	<p class="clear">
        <input type="submit" class="abutton" value="Save"  />
		<?php if ($this->viaajax): ?>
		<input type="button" class="abutton" onclick="Relapse.closeDialog('clientdialog');" value="Close" />
		<?php else: ?>
		<input type="button" class="abutton" onclick="history.go(-1);" value="Close" />
		<?php endif; ?>
    </p>
</form>