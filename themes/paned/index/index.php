<div>
	<ul id="MainActions" class="horizontalList">
		<li>
			<a class="block targeted" target="CenterPane" href="<?php echo build_url('client', 'list')?>">Clients</a>
		</li>
		<li>
			<a class="block targeted" target="CenterPane" href="<?php echo build_url('project', 'list')?>">Projects</a>
		</li>
		<li>
			<a class="block targeted" title="All Issues" target="CenterPane" href="<?php echo build_url('issue', 'list')?>">Issues</a>
		</li>
		<li>
			<a class="block targeted" title="My Tasks" target="CenterPane" href="<?php echo build_url('task', 'list')?>">Tasks</a>
		</li>
	</ul>
	<div class="clear"></div>

	<div id="LayoutContainer">
		<div id="LeftPane" class="ui-layout-west">
		</div>
		<div id="CenterPane" class="ui-layout-center">
		</div>
		<div id="RightPane" class="ui-layout-east">
		</div>
	</div>

	<script type="text/javascript">
		var PANE_FAVOURITES = [];

		<?php foreach ($this->favourites as $fav): ?>
		PANE_FAVOURITES.push({url: '<?php echo $fav->url ?>', title: "<?php $this->o($fav->title) ?>", pane: '<?php echo $fav->pane ?>', id: <?php echo $fav->id ?>});
		<?php endforeach; ?>
	</script>
</div>