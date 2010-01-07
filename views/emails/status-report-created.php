Hi <?php $this->o($this->user->username) ?>,

The following projects have status reports waiting for you to complete

<?php foreach ($this->projects as $project): ?>
* <?php echo $project->title; ?> - <?php echo build_url('project', 'view', array('id'=>$project->id, '#status'), true)?>

<?php endforeach; ?>



This is an automatically generated email from <?php echo build_url('index', 'index', null, true) ?>