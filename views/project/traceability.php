
<?php if ($this->model->id): ?>
<div id="parent-links">
    <a title="Project" href="<?php echo build_url('project', 'view', array('id'=>$this->model->id));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>
<?php endif; ?>

<h2>Traceability for &quot;<?php $this->o($this->model->title);?>&quot;</h2>

<h4>Linked <?php $this->o($this->dir)?> <?php $this->o(get_class($this->linkedFrom))?> <?php $this->o($this->linkedFrom->title); ?></h4>

<table class="item-table" cellpadding="0" cellspacing="0">
<thead>
</tr>
	<th width="10%"></th>
	<th width="60%">Title</th>
	<th width="20%">Type</th>
	<th width="10%"></th>
	</tr>
</thead>
<tbody>
<?php foreach ($this->items as $item): ?>
	<tr>
		<td>
			<a href="<?php echo build_url('project', 'traceability', array('id' => $this->model->id, 'type'=>get_class($item),'targetid'=>$item->id, 'dir'=>'to'));?>">
			Backward
			</a>
		</td>
		<td><a href="<?php echo build_url(mb_strtolower(get_class($item)), 'edit', array('id'=>$item->id))?>"><?php $this->o($item->title); ?></a></td>
		<td><?php $this->o(mb_strtolower(get_class($item))); ?></td>
		<td>
			<a href="<?php echo build_url('project', 'traceability', array('id' => $this->model->id, 'type'=>get_class($item),'targetid'=>$item->id, 'dir'=>'from'));?>">
			Forward
			</a>
		</td>
	</tr>
<?php endforeach; ?>
<?php if (count($this->items) == 0): ?>
	<tr>
		<td>
			<?php if ($this->dir == 'from'): ?>
			<a href="<?php echo build_url('project', 'traceability', array('id' => $this->model->id, 'type'=>get_class($this->linkedFrom),'targetid'=>$this->linkedFrom->id, 'dir'=>'to'));?>">
			Back
			</a>
			<?php endif; ?>
		</td>
		<td>Nothing found</td>
		<td></td>
		<td>
			<?php if ($this->dir == 'to'): ?>
			<a href="<?php echo build_url('project', 'traceability', array('id' => $this->model->id, 'type'=>get_class($this->linkedFrom),'targetid'=>$this->linkedFrom->id, 'dir'=>'from'));?>">
			Forward
			</a>
			<?php endif; ?>
		</td>
	</tr>
<?php endif; ?>
</tbody>
</table>