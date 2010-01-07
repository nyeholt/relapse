
<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="50%">Title</th>
        <th>Author</th>
        <th>Created</th>
        <th>From Rev.</th>
        <th>To Rev.</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->items as $item): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?> status-<?php $this->o(mb_strtolower(str_replace(' ', '-', $item->status)));?>">
        <td>
        <a href="<?php echo build_url('codereview', 'view', array('id'=>$item->id))?>"><?php $this->o($this->ellipsis($item->title, 40));?></a>
        </td>
        <td><?php $this->o($this->ellipsis($item->author, 40));?></td>
        <td><?php $this->o(date('Y-m-d', strtotime($item->created)));?></td>
        <td><?php $this->o($item->previousrevision)?></td>
        <td><?php $this->o($item->revision)?></td>
        <td>
        	<a href="<?php echo build_url('codereview', 'edit', array('id'=>$item->id))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('codereview', 'delete', array('id'=>$item->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php if ($this->listSize) $this->pager($this->totalCount, $this->listSize, $this->pagerName); ?>
