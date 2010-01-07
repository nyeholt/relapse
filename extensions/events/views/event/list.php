
<h3>
Events
</h3>

<ul class="item-list" cellpadding="0" cellspacing="0"> 
    <?php $index=0; foreach ($this->items as $item): ?>
    <li class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
    	<label>Title</label><p><?php $this->o($item->title);?></p>
    	<label>Date</label><p><?php $this->o(date('D j M, o \a\t H:i a', strtotime($item->eventdate)));?></p>
    	<label>Location</label><p><?php $this->o($item->location);?></p>
    	
    	<a href="<?php echo build_url('event', 'view', array('id'=>$item->id))?>">Details</a>
    </li>
    <?php endforeach; ?>
</ul>


