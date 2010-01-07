	<form method="post" action="<?php echo build_url('event', 'addinvitee');?>">
	<input type="hidden" value="<?php echo $this->model->id?>" name="id" />
	<?php $this->selectList('People', 'people', $this->userList, '', 'id', array('firstname', 'lastname', 'email'), 15, false, "style='width: 400px;'"); ?>
	<input type="submit" class="abutton" value="Add" style="margin-left: 130px"/>
	<div class="clear"></div>
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
	    <?php $index=0; foreach ($this->model->getInvitees() as $item): ?>
	    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
	        <td><?php $this->o($item->firstname.' '.$item->lastname . '('.$item->email.')');?></td>
	        <td>
	        	<div>
	        	<form method="post" action="<?php echo build_url('event', 'removeinvitee')?>">
	        		<input type="hidden" name="eventid" value="<?php $this->o($this->model->id)?>" />
	        		<input type="hidden" name="eventuserid" value="<?php $this->o($item->id)?>" />
	        		<input type="image" src="<?php echo resource('images/delete.png')?>" />
	        	</form>
	        	<form method="post" action="<?php echo build_url('event', 'addattendee')?>">
		        		<input type="hidden" name="eventid" value="<?php $this->o($this->model->id)?>" />
		        		<input type="hidden" name="eventuserid" value="<?php $this->o($item->id)?>" />
		        		<input type="image" src="<?php echo resource('images/add.png')?>" />
		        	</form>
	        	</div>
	        </td>
	    </tr>
	    <?php endforeach; ?>
	    </tbody>
	</table>

<script type="text/javascript">
	
		var indexTab = $("#invitee-index");
		if (indexTab) {
			indexTab.html("Invitees (<?php echo count($this->model->getInvitees())?>)");
		}
</script>