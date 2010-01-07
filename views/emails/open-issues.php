Hi <?php $this->o($this->user->username) ?>,

The following requests are currently open:

<?php foreach ($this->issues as $issue): ?>
<?php echo $issue->title?> : <?php echo build_url('issue', 'edit', array('id'=>$issue->id), true); ?>


<?php endforeach; ?>

This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>