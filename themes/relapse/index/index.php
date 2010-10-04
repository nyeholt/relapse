
<div>
	<div class="std">
		<ul class="largeDualList">
			<li>
				<a class="block" href="<?php echo build_url('client', 'index')?>">Clients</a>
			</li>
			<li>
				<a class="block" href="<?php echo build_url('project', 'index')?>">Projects</a>
			</li>
		</ul>
		<div class="clear"></div>
	</div>

	<div class="std">
		<?php include dirname(__FILE__).'/../task/list.php'; ?>
	</div>

	<?php include dirname(__FILE__).'/../issue/issue-list.php'; ?>

	<div class="std">
		<h2>Projects</h2>
		<?php include dirname(__FILE__).'/../project/list.php'; ?>
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
</div>