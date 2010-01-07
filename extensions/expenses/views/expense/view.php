<html>
<head>
<style type="text/css">
body {
	font-family: Helvetica;
}

.expense-image{ 
	page-break-before: always;
	margin: 20px;
}
</style>
</head>
<body>

<h2>
Expenses
</h2>

<table>
  <tr>
    <?php if ($this->client): ?><th>Client Name</th><?php endif;?>
    <?php if ($this->user): ?><th>Name</th><?php endif;?>
    <th>Date</th>
    <th>From</th>
    <th>To</th>
  </tr>
  <tr>
    <?php if ($this->client): ?><td><?php $this->o($this->client->title);?></td><?php endif;?>
    <?php if ($this->user): ?><td><?php $this->o($this->user->firstname.' '.$this->user->lastname);?></td><?php endif;?>
    <td><?php echo date('d-M-Y', time());?></td>
    <td><?php echo date('d-M-Y', strtotime($this->start));?></td>
    <td><?php echo date('d-M-Y', strtotime($this->end));?></td>
  </tr>
</table>


<table class="item-table" cellpadding="5" cellspacing="0">
    <thead>
    <tr>
	    <?php if ($this->client): ?><th>Name</th><?php endif;?>
        <?php if ($this->user): ?><th>Client</th><?php endif;?>
        <th>Expense Date</th>
        <th>Location</th>
        <th>Project</th>
        <th width="30%">Description</th>
        <th>GST</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    <?php 
    $index=0; 
    $total = 0;
    foreach ($this->expenses as $expense): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
    	<?php if ($this->client): ?><td><?php $this->o($expense->getFirstName().' '.$expense->getLastName());?></td><?php endif;?>
        <?php if ($this->user): ?><td><?php $this->o($expense->getClientTitle());?></td><?php endif;?>
        <td><?php $this->o(date('Y-m-d', strtotime($expense->expensedate)));?></td>
        <td><?php $this->o($expense->location)?></td>
        <td><?php $this->o($expense->getProjectTitle())?></td>
        <td><?php $this->o($expense->description);?></td>
        <td><?php 
        if ($expense->amount > 0) {
            echo sprintf("$%.2f", ($expense->amount / 1.1) * .1);
        } else {
            echo '&nbsp;';
        }
        ?>
        </td>
        <td><?php $total+=$expense->amount; $this->o(sprintf("$%.2f", $expense->amount))?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
    	<td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><?php 
        if ($total > 0) {
            echo sprintf("$%.2f", ($total / 1.1) * .1);
        } else {
            echo '&nbsp;';
        }
        ?>
        </td>
        <td><?php $this->o(sprintf("$%.2f", $total))?></td>
    </tr>
    </tbody>
</table>

<?php foreach ($this->expenses as $expense): 
    $files = $expense->getFiles();
    foreach ($files as $file) {
        
        ?>
        <div class="expense-image">
        <?php if ($file->isImage()): ?>
        	<p>
	        	<?php $this->o($file->filename.': '.$expense->description) ?> 
	        </p>
        	<?php if ($this->mode == 'pdf' && za()->getConfig('require_http_auth')): ?>
			<img src="<?php echo url_login(za()->getConfig('http_user'), za()->getConfig('http_pass'), build_url('file', 'view', array('id' => $file->id), true)).$file->filename.'?user='.current_user()->getUsername().'&amp;ticket='.current_user()->getTicket()?>" />        
			<?php else: ?>
			<img src="<?php echo  build_url('file', 'view', array('id' => $file->id), true).$file->filename.'?user='.current_user()->getUsername().'&amp;ticket='.current_user()->getTicket()?>" />        			
			<?php endif; ?>
	        
        <?php else: ?>
        	<p>
	        	<?php $this->o($file->filename.' '.$expense->description) ?> 
	        </p>
        	<a href="<?php echo build_url('file', 'view', array('id' => $file->id), true).$file->filename.'?user='.current_user()->getUsername().'&amp;ticket='.current_user()->getTicket()?>">
        	Download this file
        	</a>
        <?php endif; ?>
        </div>
        <?php
    }

endforeach; ?>
</body>
</html>