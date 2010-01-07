<script type="text/javascript">
    $().ready(function(){
        $("#mailout-container").tabs({ fxFade: true, fxSpeed: 'fast' });
    });
    
</script>

<h2>
<?php $this->o($this->model->id ? 'Edit "'.$this->model->title.'"' : 'New Mailout');?>
</h2>


<div id="mailout-container">
	<ul class="tab-options">
        <li><a href="#details"><span>Details</span></a></li>
        <?php if ($this->model->id): ?>
        <li><a href="#mail-options"><span id="options-index">Options</span></a></li>
        <li><a href="#recipients"><span id="recipients-index">Recipients</span></a></li>
        <?php endif; ?>
    </ul>
    
    
	<div id="details">
		<form class="mail-form" method="post" action="<?php echo build_url('mailout', 'save');?>">
		
		<?php if ($this->model->id): ?>
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php endif; ?>
			<?php $this->textInput('Title', 'title')?>
			<?php $this->calendarInput('Mail Date', 'tomail') ?>
			
			<?php if ($this->model->maildate != null): ?>
			<p>
			<label>Sent</label>
			<?php $this->o($this->u()->formatDate($this->model->maildate)) ?>
			</p>
			<?php endif;?> 
			<?php $this->tinymceInput('HTML', 'html') ?>
			<input type="submit" value="Save" accesskey="s" class="abutton"></input>
		</form>
		<?php if ($this->model->id): ?>
			
		<?php endif; ?>
		
	</div>

	<?php if ($this->model->id): ?>
	<div id="mail-options">
		<?php if ($this->model->id): ?>
		<form method="post" action="<?php echo build_url('mailout', 'preview')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<p>
			<label for="previewemail">Preview:</label>
			<input id="previewemail" type="text" name="email" /><input class="abutton" type="submit" value="Preview" />
			</p>
		</form>
		<p>Clicking below will immediately send all emails, which may take some time. Please do not click more than once!</p>
		<form method="post" action="<?php echo build_url('mailout', 'sendEmail')?>" onsubmit="$('#send-mail-button').attr('disabled', true).css('color', '#fff'); return true">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<input type="submit" value="Send Email" class="abutton" id="send-mail-button"></input>
		</form>
		<!--  <form method="post" action="<?php echo build_url('mailout', 'sendEmail')?>">
			<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
			<p>
			<input class="abutton" type="submit" value="Send Email" />
			</p>
		</form> -->
		<?php endif; ?>
	</div>
	
	
	<div id="recipients">
		<form method="post" action="<?php echo build_url('mailout', 'addrecipient');?>">
		<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
		<?php $this->selectList('People', 'people', $this->userList, '', 'id', array('firstname', 'lastname', 'username'), 20, false, 'style="width: 500px;"'); ?>
		<p class="clear">
		<input type="submit" class="abutton" value="Add" />
		</p>
		
		</form>
		
		<table class="item-table" cellpadding="0" cellspacing="0">
		    <thead>
		    <tr>
		    	<th>Name</th>
		        <th width="15%">&nbsp;</th>
		    </tr>
		    </thead>
		    <tbody>
		    <?php $index=0; foreach ($this->model->getRecipients() as $item): ?>
		    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
		        <td><?php $this->o($item->firstname.' '.$item->lastname . '('.$item->username.')');?></td>
		        <td>
		        	<div>
		        	<form method="post" action="<?php echo build_url('mailout', 'removerecipient')?>">
		        		<input type="hidden" name="id" value="<?php $this->o($this->model->id)?>" />
		        		<input type="hidden" name="recipientid" value="<?php $this->o($item->id)?>" />
		        		<input type="image" src="<?php echo resource('images/delete.png')?>" />
		        	</form>
		        	</div>
		        </td>
		    </tr>
		    <?php endforeach; ?>
		    </tbody>
		</table>
	</div>
	<?php endif ; ?>
</div>