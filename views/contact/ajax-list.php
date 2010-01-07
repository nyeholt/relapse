
<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="35%">Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Phone</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($this->contacts as $contact): ?>
    <tr>
    	<td>
    	<a href="<?php echo build_url('contact', 'edit', array('clientid' => $this->client->id, 'id'=> $contact->id))?>">
        <?php $this->o($contact->firstname. ' '.$contact->lastname)?>
		</a>
    	</td>
    	<td>
    		<a href="mailto:<?php echo $contact->email?>"><?php echo $contact->email?></a>
    	</td>
    	<td>
    	<?php $this->o($contact->mobile) ?>
    	</td>            
		<td>
            <?php $this->o($contact->directline) ?>
		</td>
    	<td>
<?php if ($this->u()->hasRole(User::ROLE_USER)): ?>
<a title="Delete contact" onclick="if (confirm('Are you sure?')) location.href='<?php echo build_url('contact', 'delete', array('id' => $contact->id))?>'; return false;" href="#"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
<?php endif; ?>
    	</td>
    </tr>
<?php endforeach;?>
	</tbody>
</table>


<script type="text/javascript">
	$().ready(function() {
		var contactsIndex = $("#contacts-index");
		if (contactsIndex) {
			contactsIndex.html("Contacts (<?php echo count($this->contacts)?>)");
		}
	});
</script>