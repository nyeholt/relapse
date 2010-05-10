<h2>What are people reading?</h2>
<p>To add to this list, tag something with "lmintra" on delicious!</p>
<div id="bookmarks">
	<ul>
		<?php $posts = $this->feed->getPosts(); 
		for ($i = 0, $c = count($posts); $i < $c; $i++): ?>
			<li class="dynamic-bookmark">
			<?php  ?>
				<p>
				<a target="_blank" href="<?php $this->o($posts[$i]['link']); ?>"><?php $this->o($posts[$i]['title']); ?></a>
				</p>
				
			</li>
		<?php endfor; ?>
		</ul>
	
</div>