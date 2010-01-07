<p>
<a href="<?php php(); ?> echo build_url('<?php echo $modelType;?>', 'edit');<?php unphp();?>">Create new <?php echo $name;?></a>
</p>

<table class="list">
    <thead>
    <tr>
        <th>
            Title
        </th>
        <th>
            View
        </th>
        <th>
            Edit
        </th>
        <th>
            Delete
        </th>
    </tr>
    </thead>
    <tbody>
    <?php php();?> $index = 0; foreach ($this->items as $item): <?php unphp();?>
    <tr class="<?php php();?> echo $index++ % 2 == 0 ? 'even' : 'odd'<?php unphp();?>">
        <td>
            <?php php();?> $this->o($item->title)<?php unphp();?>
        </td>
        <td class="centered">
            <a title="View" href="<?php php();?> echo build_url('<?php echo $modelType;?>', 'view', array('id'=>$item->id), null, 'default')<?php unphp();?>"><img src="<?php php();?> echo resource('images/eye.png')<?php unphp();?>" /></a>
        </td>
        <td class="centered">
            <a title="Edit" href="<?php php();?> echo build_url('<?php echo $modelType;?>', 'edit', array('id'=>$item->id))<?php unphp();?>"><img src="<?php php();?> echo resource('images/pencil.png')<?php unphp();?>" /></a>
        </td>
        <td class="centered">
            <a title="Delete" href="#" onclick="if (confirm('Are you sure?')) location.href='<?php php();?> echo build_url('<?php echo $modelType;?>', 'delete', array('id'=>$item->id))<?php unphp();?>; return false;'"><img src="<?php php();?> echo resource('images/delete.png')<?php unphp();?>" /></a>
        </td>
    </tr>
    <?php php();?> endforeach; <?php unphp();?>
    </tbody>
</table>
