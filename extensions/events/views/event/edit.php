<script type="text/javascript">
    $().ready(function(){
        $("#event-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
    
</script>


<h2>
<?php $this->o($this->model->id ? 'Edit "'.$this->model->title.'"' : 'New Event');?>
</h2>

<div id="event-container">
	<ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->id): ?>
        <li><a href="#invitees"><span id="invitee-index">Invitees</span></a></li>
        <li><a href="#attendees"><span id="attendee-index">Attendees</span></a></li>
        <li><a href="#invitemail"><span>Emails</span></a></li>

        <?php endif; ?>
    </ul>
    
	<div id="details">
		<form class="event-form" method="post" action="<?php echo build_url('event', 'save');?>">
		
		<?php if ($this->model->id): ?>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php endif; ?>
		
			<?php $this->textInput('Title', 'title'); ?>
			<?php $this->valueList('Location', 'location', 'event-form', $this->locations) ?>
			<?php $this->calendarInput('Date', 'eventdate', '', true); ?>
			<?php $this->timeInput('Start Time', 'starttime', 'event-form') ?>
			<?php $this->timeInput('End Time', 'endtime', 'event-form') ?>
			    
		    <?php $this->textInput('Max attendees', 'maxattendees'); ?>
		    
		    <?php $this->calendarInput('Invite On', 'inviteon') ?>
		    <?php $this->yesNoInput('Public?', 'ispublic'); ?>
		    
			<?php $this->tinymceInput('Description', 'description'); ?>
			
			<?php $this->tinymceInput('Post Event', 'postevent'); ?>
		<p class="clear">
		    <input type="submit" class="wymupdate abutton" value="Save" accesskey="s" />
		    <input type="button" class="abutton" onclick="location.href='<?php echo build_url('event')?>'" value="Done" />
		</p>
		</form>
	</div>
	
	<?php if ($this->model->id): ?>
	<div id="invitees">
		<?php $this->dispatch('event', 'listinvitees', array('id'=>$this->model->id)); ?>
	</div>
	<div id="attendees">
		<?php $this->dispatch('event', 'listattendees', array('id'=>$this->model->id)); ?>
	</div>
	
	<div id="invitemail">
		<p>Note: The following dynamic variables can be used in the emails</p>
		<dl>
			<dt>{firstname}</dt>
			<dd>The recipient's first name</dd>
			<dt>{lastname}</dt>
			<dd>The recipient's last name</dd>
			<dt>{responseurl}</dt>
			<dd>The url the recipient should click to register</dd>
			<dt>{dontspamme}</dt>
			<dd>The url the recipient should click to opt out of further notifications</dd>
		</dl>
		<p>The "Invitation" email is sent out to users when the event is open for registration. 
		The "Last Chance" email is sent out if there are places open 2 weeks before
		the event. The "Reminder" email is sent out to all registered users 1 week
		before the event to remind them that the event is on.</p>
		<form class="wide-form" method="post" action="<?php echo build_url('event', 'save');?>">
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<?php $this->textInput('Invitation Email', 'inviteemail', 20); ?>

			<?php $this->textInput('Last Chance Email', 'lastchanceemail', 10); ?>
			<?php $this->textInput('Reminder Email', 'reminderemail', 10); ?>
			<input type="submit" class="abutton" value="Save Emails" />
		</form>
		<form method="post" action="<?php echo build_url('event', 'previewemail')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<p>
		    <label>Send Preview To</label>
		    <input class="input" type="text" name="sendto" />
  		    <input class="abutton" type="submit" value="Preview"/>
		    </p>
		</form>
	</div>
	<?php endif;?>
</div>
