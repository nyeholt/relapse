<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Id</th>
        <th>Created</th>
        <th>Action Name</th>
        <th>Action ID</th>
        <th>Data</th>
        <th>User</th>
        <th>Remote Ip</th>
        <th>URL Accessed</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->entries as $item): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><?php $this->o($item->id);?></td>
        <td><?php $this->o($item->created);?></td>
        <td><?php $this->o($item->actionname);?></td>
		<td><?php $this->o($item->actionid);?></td>
		<td><?php $this->o($item->entrydata);?></td>
		<td><?php $this->o($item->user);?></td>
		<td><?php $this->o($item->remoteip);?></td>
		<td><?php $this->o($item->user);?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->pager($this->totalEntries, 50, $this->pagerName); ?>