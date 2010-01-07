	<table class="item-table" cellpadding="0" cellspacing="0">
	    <thead>
	    <tr>
	        <th width="50%">Name</th>
	        <th>Referrer</th>
	        <th width="15%">&nbsp;</th>
	    </tr>
	    </thead>
	    <tbody>
	    <?php $index=0; foreach ($this->model->getAttendees() as $item): ?>
	    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
	        <td><?php $this->o($item->firstname.' '.$item->lastname . '('.$item->username.')')?></td>
	        <td><?php $this->o($item->referer)?></td>
	        <td>
	             <form method="post" action="<?php echo build_url('event', 'removeattendee')?>">
	        		<input type="hidden" name="eventid" value="<?php $this->o($this->model->id)?>" />
	        		<input type="hidden" name="eventuserid" value="<?php $this->o($item->id)?>" />
	        		<input type="image" src="<?php echo resource('images/delete.png')?>" />
	        	</form>
	        </td>
	    </tr>
	    <?php endforeach; ?>
	    </tbody>
	</table>
<script type="text/javascript">
		var indexTab = $("#attendee-index");
		if (indexTab) {
			indexTab.html("Attendees (<?php echo count($this->model->getAttendees())?>)");
		}
</script>