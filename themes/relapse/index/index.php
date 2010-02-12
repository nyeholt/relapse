
<div>
	<div class="std">
		<ul class="largeDualList">
			<li>
				<a class="block" href="#" onclick="javascript: $('#dialogdiv').simpleDialog({title: 'Create new task', modal: false, url: '<?php echo build_url('task', 'edit') ?>'}); return false; ">Add task</a>
			</li>

			<li>
			</li>
			<li>
			</li>
			<li>
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
					<?php echo $this->ellipsis($this->escape($item->title), 18);?>
					<img onclick="$.post('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $item->id?>', type:'<?php echo get_class($item)?>'}, function() {$('#<?php echo $rowId?>').remove();}); return false; " alt="unbsubscribe from resource" src="<?php echo resource('images/thumb_down.png')?>"/>
				</a>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="clear"></div>
	</div>
</div>
<div class="std dialog" id="dialogdiv"></div>