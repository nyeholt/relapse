Hi <?php $this->o($this->user->username) ?>,

The following expenses have been paid

<?php foreach ($this->expenses as $expense): ?>
* <?php echo sprintf("$%.2f", $expense->amount) . ' - ' . $expense->description ?>
<?php endforeach; ?>


This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>