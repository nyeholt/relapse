<h2>Contacts</h2>


<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="35%">Name</th>
        <th>Email</th>
        <th>Phone Number</th>
        <th width="20%">Company</th>
        <th width="5%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->contacts as $contact): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td>
		<a href="<?php echo build_url('contact', 'edit', array('id'=>$contact->id, 'clientid'=>$contact->clientid))?>">
        <?php $this->o($contact->firstname);?> <?php $this->o($contact->lastname)?>
		</a>
        </td>
        <td><a href="mailto:<?php $this->o($contact->email);?>"><?php $this->o($contact->email);?></a></td>
        <td><?php $this->o($contact->mobile);?></td>
        <td><a href="<?php echo build_url('client', 'view', array('id'=>$contact->clientid))?>"><?php $this->o($this->ellipsis($contact->company));?></a></td>
        <td>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('contact', 'delete', array('id'=>$contact->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->AtoZPager($this->letters, $this->pagerName, true); ?>

<p>
<a class="abutton" href="<?php echo build_url('contact', 'contactimport')?>">Import Contacts</a> 
</p>