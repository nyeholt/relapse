<ul>
<?php foreach ($this->invoices as $invoice): ?>
    <li>
        <a class="action-icon" href="<?php echo build_url('invoice', 'viewinvoice', array('id'=>$invoice->id));?>"><img class="small-icon" src="<?php echo resource('images/eye.png');?>" /></a>
        <a title="Edit Invoice" href="<?php echo build_url('invoice', 'edit', array('id' => $invoice->id, 'projectid'=>$invoice->projectid))?>">
        <?php $this->o($invoice->title);?> 
        </a>
    </li>
<?php endforeach; ?>
</ul>