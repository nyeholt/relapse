
<div>
	<div class="std">
		<h2>Do</h2>
		<ul class="large-list">
			<li>
				<a href="#" onclick="javascript: $('#dialogdiv').simpleDialog({modal: false}); return false; ">Click here</a>
			</li>

			<li>
			</li>
		</ul>
	</div>
	<div class="std">
		<h2>Go to...</h2>
		<ul class="large-list" id="subscribed-items">
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
				<?php $this->hierarchy($hierarchy, '&raquo;', null); ?>
				&raquo;
				<a href="<?php echo build_url($itemType, 'view', array('id'=>$item->id));?>"><?php $this->o($item->title);?></a>
				
				<a title="Unsubscribe" id="delete-project-watch" href="#" onclick="$.post('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $item->id?>', type:'<?php echo get_class($item)?>'}, function() {$('#<?php echo $rowId?>').remove();}); return false; ">
					<img alt="unbsubscribe from resource" src="<?php echo resource('images/thumb_down.png')?>"/>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<div class="std dialog" id="dialogdiv"></div>

<script type="text/javascript">
$().ready(function () {
	
});
</script>