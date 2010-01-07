<?php 
    $pid = isset($this->parent) ? $this->parent->id : 'root';
    $type = isset($this->listType) ? $this->listType : 'list';
    $tmpFeature = null;
?>

<ol id="feature-<?php echo $pid?>-list">
<?php foreach ($this->features as $feature): ?>
<?php $tmpFeature = $feature;?>
    <li class="feature-<?php echo $pid?>-item" id="<?php echo $type?>-feature-<?php echo $feature->id?>">
        <a class="action-icon" title="Edit Feature" href="<?php echo build_url('feature', 'edit', array('id' => $feature->id, 'projectid'=>$feature->projectid))?>"><img class="small-icon" src="<?php echo resource('images/pencil.png')?>" /></a>
        <a class="action-icon" title="Delete Feature" href="#" onclick="if (confirm('Are you sure')) location.href='<?php echo build_url('feature', 'delete', array('id' => $feature->id))?>'; return false;"><img class="small-icon" src="<?php echo resource('images/delete.png')?>" /></a>
        <a class="action-icon" title="Add Child Feature" href="<?php echo build_url('feature', 'edit', array('parent' => $feature->id, 'projectid'=>$feature->projectid))?>"><img class="small-icon" src="<?php echo resource('images/page_copy.png')?>" /></a>
 
        <a id="<?php echo $type?>-feature-<?php echo $feature->id?>-link" title="Show Details" href="#"><?php $this->o($feature->title)?> (<?php $this->o($feature->estimated)?>)</a> 
        
        <div id="<?php echo $type?>-feature-<?php echo $feature->id?>-children">
        </div>

        <script type="text/javascript">
            $('#<?php echo $type?>-feature-<?php echo $feature->id?>-link').click(
                function() {
                    $.get('<?php echo build_url('feature', 'loadchildren')?>', {id: '<?php echo $feature->id?>', projectid: '<?php echo $feature->projectid?>'}, function(data) {
                        $('#<?php echo $type?>-feature-<?php echo $feature->id?>-children').html(data);
                    });
                    return false;
                }
            );
        </script>
    </li>
<?php endforeach; ?>
</ol>
<?php if($tmpFeature != null): ?>
<p>
<a href="<?php echo build_url('feature', 'orderfeatures', array('id'=>$tmpFeature->id))?>" title="Edit feature order" >Order Features</a>
</p>

<?php endif; ?>