
<div>
	<div class="std">
		<ul class="largeDualList">
			<li>
				<?php $this->dialogPopin('cleintdialog', 'Add Client', build_url('client', 'edit', array('_ajax'=> 1)), array('title' => 'Create new client'), 'class="block"'); ?>
			</li>
			<li>
				<?php $this->dialogPopin('projectdialog', 'Add Project', build_url('project', 'edit', array('_ajax'=> 1)), array('title' => 'Create new project'), 'class="block"'); ?>
			</li>
			<li>
				<?php $this->dialogPopin('taskdialog', 'Add Task', build_url('task', 'edit', array('_ajax'=> 1)), array('title' => 'Create new task'), 'class="block"'); ?>
			</li>
		</ul>
		<div class="clear"></div>
	</div>
	
	<div class="std">
		<h2>Go to...</h2>
		<ul class="largeDualList" id="subscribed-items">
		<?php
		foreach ($this->items as $item): 
			$itemType = mb_strtolower(get_class($item));
			// if ($itemType == 'project' || $itemType == 'issue' || $itemType=='task') {
				$rowId = 'item-'.$itemType.'-'.$item->id;
				$hierarchy = array();
				if (method_exists($item, 'getHierarchy')) {
					$hierarchy = $item->getHierarchy();
				}
			?>
			<li id="<?php $this->o($rowId)?>">
				<?php // $this->hierarchy($hierarchy, '&raquo;', null); ?>
				<div >
				<a class="block" href="<?php echo build_url($itemType, 'view', array('id'=>$item->id));?>">
					<?php echo $this->ellipsis($this->escape($item->title), 20);?>
					<img onclick="$.post('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $item->id?>', __validation_token: '<?php echo $this->requestValidator(true)?>',  type:'<?php echo get_class($item)?>'}, function() {$('#<?php echo $rowId?>').remove();}); return false; " alt="unbsubscribe from resource" src="<?php echo resource('images/thumb_down.png')?>"/>
				</a>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="clear"></div>
	</div>

	<div class="std">
		<h2>Latest</h2>
		<ul class="largeDualList">
			<?php foreach ($this->latest as $l): ?>
			<li><a class="block" href="<?php echo build_url('project', 'view', array('id'=> $l->id))?>"><?php echo $this->ellipsis($this->escape($l->title), 22);?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>