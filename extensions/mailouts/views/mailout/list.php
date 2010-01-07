<h3>
Mailouts
</h3>

<table class="item-table" cellpadding="0" cellspacing="0"> 
<thead>
	<tr>
	<th width="70%">Title</th>
	<th>Date</th>
	</tr>
</thead>
<tbody>
    <?php $index=0; foreach ($this->items as $item): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
    	<td><a href="<?php echo build_url('mailout', 'edit', array('id'=>$item->id))?>"><?php $this->o($item->title);?></a></td>
    	<td><?php $this->o(date('D j M, o \a\t H:i a', strtotime($item->tomail)));?></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
<p>
<a href="<?php echo build_url('mailout', 'edit')?>" class="abutton">Add Mailout</a>
</p>

