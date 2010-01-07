<?php if ($this->feed != null): ?>
<h2>What are people reading? [<a href="<?php echo build_url('index', 'bookmark')?>">All</a>]</h2>
<p>To add to this list, tag something with "lmintra" on delicious!</p>
<div id="bookmarks">
	<div class="inner-column">
		<ul>
		<?php $posts = $this->feed->getPosts(); 
		for ($i = 0, $c = count($posts); $i < $c && $i < 5; $i++): 
		$date = $posts[$i]['date'];
		$showNew = false;
		
		if (mb_strlen($date)) {
		    if (strtotime($date) > strtotime($this->u()->getLastLogin())) {
		        $showNew = true;
		    }
		}
		?>
			<li class="dynamic-bookmark">
			<?php  ?>
				<p>
				<a target="_blank" href="<?php $this->o($posts[$i]['link']); ?>"><?php $this->o($posts[$i]['title']); ?></a>
				<?php if ($showNew): ?>
					<img src="<?php echo resource('images/new.png')?>" />
				<?php endif; ?>
				</p>
				
			</li>
		<?php endfor; ?>
		</ul>
	</div>
	<div class="inner-column">
		<ul>
		<?php 
		for ($c = count($posts); $i < $c && $i < 10; $i++): 
		$date = $posts[$i]['date'];
		$showNew = false;
		
		if (mb_strlen($date)) {
		    if (strtotime($date) > strtotime($this->u()->getLastLogin())) {
		        $showNew = true;
		    }
		}
		?>
			<li class="dynamic-bookmark">
			<?php  ?>
				<p>
				<a href="<?php $this->o($posts[$i]['link']); ?>"><?php $this->o($posts[$i]['title']); ?></a>
				<?php if ($showNew): ?>
					<img src="<?php echo resource('images/new.png')?>" />
				<?php endif; ?>
				</p>
				
			</li>
		<?php endfor; ?>
		</ul>
	</div>
</div>
<?php endif;?>
<div class="clear" />
<div>
    <h2>Your Projects</h2>
    <table class="large-item-table" cellpadding="0" cellspacing="0">
	    <thead>
	    <tr>
	        <th width="50%">Project Name</th>
	        <th width="5%"></th>
	    </tr>
	    </thead>
	    <tbody>
    <?php 
    foreach ($this->projects as $project) {
        ?>
        <tr>
	        <td width="50%"><a href="<?php echo build_url('project', 'view', array('id'=>$project->id));?>"><?php $this->o($project->title);?></a></h3></td>
	        <td width="5%"><a href="<?php echo build_url('project', 'leave', array('id'=>$project->id))?>" style="float: right" class="abutton" title="Leav Project">Leave Project</a></td>
	    </tr>
        <?php 
    }
    ?>
    	</tbody>
    </table>
</div>

<div style="clear: left;"></div>
