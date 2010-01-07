
<h2>Users</h2>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>User</th>
        
		<?php if ($this->u()->isPower()): ?>
        <th width="50px"></th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->users as $user): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td style="font-size: 120%">
		<a title="View Expenses" href="<?php echo build_url('index', 'listforuser', array('username' => $user->getUsername()));?>"><?php $this->o($user->firstname.' '.$user->lastname);?></a>
		</td>
		<?php if ($this->u()->isPower()): ?>
		<td>
		<a title="Edit Expenses" href="<?php echo build_url('expense', 'listforuser', array('username' => $user->getUsername()), false, 'default');?>"><img src="<?php echo resource('images/coins.png')?>" /></a>		
		</td>
		<?php endif; ?>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>