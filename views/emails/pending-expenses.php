Hi <?php $this->o($this->user->username) ?>,

The following expenses are still awaiting review:

<?php foreach ($this->expenses as $expense): ?>

<?php $this->o($expense->username.' : '.$expense->amount.' : '.$expense->description)?> @ <?php echo build_url('client', 'view', array('id' => $expense->clientid, '#expenses'), true) ?>


<?php endforeach; ?>

This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>