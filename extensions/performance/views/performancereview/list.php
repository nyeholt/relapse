
<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="35%">Title</th>
        <th>From</th>
        <th>To</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $item):?>
    	<tr>
	        <td>
	<a href="<?php echo build_url('performancereview', 'view', array('id' => $item->id))?>">
    <?php $this->o($item->title)?>
    </a>
	        </td>
	        <td style="text-align: center"><?php $this->o(date('Y-m-d', strtotime($item->from)))?></td>
	        <td style="text-align: center"><?php $this->o(date('Y-m-d', strtotime($item->to)))?></td>
	        <td>
	        <a href="<?php echo build_url('performancereview', 'edit', array('id' => $item->id))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
	        </td>
	    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p>
<a class="abutton" href="<?php echo build_url('performancereview', 'edit', array('username'=>$this->user->username))?>">Create Review</a>
</p>