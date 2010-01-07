<h3>
Expense Reports
</h3>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="20%">From</th>
        <th width="20%">To</th>
        <th>Title</th>
        <th>Total</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->reports as $expenseReport): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td><?php $this->o(date('Y-m-d', strtotime($expenseReport->from)));?></td>
        <td><?php $this->o(date('Y-m-d', strtotime($expenseReport->to)));?></td>
        <td><?php $this->o($expenseReport->title);?></td>
        <td>$<?php $this->o(sprintf('%.2f', $expenseReport->total));?></td>
        <td>
        <?php if ($expenseReport->locked): ?>
        	<a href="<?php echo build_url('index', 'view', array('id'=>$expenseReport->id))?>"><img src="<?php echo resource('images/eye.png');?>" title="Preview Expenses"/></a>
        	<a href="<?php echo build_url('index', 'view', array('pdf'=> 1, 'id'=>$expenseReport->id))?>"><img src="<?php echo resource('images/adobe.png');?>" title="Export to PDF"/></a>
        <?php endif; ?>
        
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

