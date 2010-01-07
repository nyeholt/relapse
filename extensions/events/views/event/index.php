
<h3>
Events
</h3>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="40%">Title</th>
        <th>Date</th>
        <th>Location</th>
        <th width="10%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->items as $item): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><a href="<?php echo build_url('event', 'edit', array('id'=>$item->id))?>"><?php $this->o($item->title);?></a></td>
        <td><?php $this->o(date('D j M, o', strtotime($item->eventdate)));?></td>
        <td><?php $this->o($item->location);?></td>
        <td>
        	<form method="post" action="<?php echo build_url('event', 'delete', array('id'=>$item->id))?>" onsubmit="return confirm('Are you sure?');">
        		<input type="hidden" name="id" value="<?php echo $item->id ?>" />
        		<input type="image" src="<?php echo resource('images/delete.png')?>" />
        	</form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p>
<p>
<a class="abutton" href="<?php echo build_url('event', 'edit')?>">New Event</a>
<a class="abutton" href="<?php echo build_url('event', 'import')?>">Import Users</a>
</p>
</p>

