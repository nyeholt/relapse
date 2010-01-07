<h2><?php $this->o($this->model->title); ?></h2>

<label>Title</label><p><?php $this->o($this->model->title);?></p>
<label>Date</label><p><?php $this->o(date('D j M, o \a\t H:i a', strtotime($this->model->eventdate)));?></p>
<label>Location</label><p><?php $this->o($this->model->location);?></p>
<label>Description</label>
<div><?php print($this->model->description);?></div>

<?php 
$attendees = $this->model->getAttendees();
if ($this->u()->hasRole(User::ROLE_PUBLIC)): ?>
	<?php if (!isset($attendees[$this->u()->id])): ?>
	<form method="post" action="<?php echo build_url('event', 'register')?>">
		<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
		<input type="submit" value="Register Now!" />
	</form>
	<?php else: ?>
	<p>You are already registered for this event</p>
	<form method="post" action="<?php echo build_url('event', 'unregister')?>">
		<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
		<input type="submit" value="Cancel Registration!" />
	</form>
	<?php endif; ?>
<?php else: ?>
	<?php $this->dispatch ('user', 'register', array('eventid' => $this->model->id)) ?>
<?php endif; ?>