<h2>
Companies -
<select name="relationships" id="relationships">
	<option value="ALL" <?php echo "ALL" == $this->relationship ? 'selected="selected"' : ''?>>ALL</option>
	<?php foreach ($this->relationships as $rel): ?>
	<option value="<?php $this->o($rel)?>" <?php echo $rel == $this->relationship ? 'selected="selected"' : ''?>><?php $this->o($rel)?></option>
	<?php endforeach; ?>
</select>
</h2>
<script type="text/javascript">
$().ready(function() {
	$('#relationships').change(function(event) {
		location.href='<?php echo build_url('client', 'index')?>?relation='+$(this).val();
	});
});
</script>
<br/>
<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Title</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->clients as $client): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><a href="<?php echo build_url('client', 'view', array('id'=>$client->id))?>"><?php $this->o($client->title);?></a></td>
        <td>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('client', 'delete', array('id'=>$client->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
            <a href="<?php echo build_url('client', 'edit', array('id'=>$client->id))?>"><img src="<?php echo resource('images/pencil.png')?>" /></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->AtoZPager($this->clientLetters, $this->clientPagerName, true, array('relation'=>$this->relationship)); ?>
<?php // $this->pager($this->totalClients, $this->clientListSize, $this->clientPagerName); ?>

<a href="<?php echo build_url('client', 'edit')?>" class="abutton">Add Client</a>
