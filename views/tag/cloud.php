<ul class="tag-list">
<?php foreach ($this->tags as $tag): ?>

<?php 
$score = round(($tag['frequency'] - $this->min) > 0 ? ($tag['frequency'] - $this->min) / $this->scale + 1 : 1);
?>
<li>
	<a class="tagsize<?php echo $score?>" href="<?php echo $this->type ? build_url($this->type, 'index', array('tag'=>$tag['tag'])) : '#'?>">
	<?php $this->o($tag['tag'])?>
	</a>
	<span class="context">(<?php echo $tag['frequency']?>)</span></li>
<?php endforeach; ?>
</ul>
