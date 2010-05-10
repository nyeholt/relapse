<h2><?php echo count($this->contacts)?> results for "<?php $this->o($this->query); ?>"</h2>
<br/>
<table class="item-table">
<thead>
	<tr>
	<th>Name</th>
	<th>Email</th>
	<th>Phone</th>
	</tr>
</thead>
<tbody>
<?php foreach ($this->contacts as $contact): ?>
	<tr>
	<td><a class="targeted" target="RightPane" href="<?php echo build_url('contact', 'edit', array('id'=>$contact->id))?>"><?php $this->o($contact->firstname.' '.$contact->lastname)?></a></td>
	<td><a href="mailto:<?php $this->o($contact->email)?>"><?php $this->o($contact->email)?></a></td>
	<td><?php $this->o($contact->mobile)?> | <?php $this->o($contact->directline)?>  </td>
	</tr>
<?php endforeach;?>
</tbody>
</table>