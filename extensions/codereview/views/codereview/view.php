<?php if ($this->model == null): ?>
<h2>That review no longer exists</h2>
<?php else: ?>
<div id="parent-links">
    <a title="Parent Project" href="<?php echo build_url('project', 'view', array('id'=>$this->model->projectid, '#codereviews'));?>"><img src="<?php echo resource('images/project.png')?>"/></a>
</div>

<h2>
<?php $this->o($this->model->title)?>
</h2>

<p>
<?php $this->o($this->model->description) ?>
</p>

<p>
	<label>Revisions</label><?php $this->o($this->model->previousrevision.' => '.$this->model->revision)?>
</p>
<hr/>
<?php 
$difflog = $this->model->getLog();
$diff = $this->model->getDiffList();
?>

<?php foreach ($difflog as $log): ?>
<p>
	<label>Author</label><?php $this->o($log['author'])?>
</p>
<p>
	<label>Date</label><?php $this->o(date('Y-m-d', strtotime($log['date'])))?>
</p>

<p>
	<label>Message</label><?php $this->o($log['msg'])?>
</p>
<div class="clear"></div>
<p>
	<label>Modified files</label>
	
</p>
<p class="clear">
	<ul>
	<?php foreach ($log['paths'] as $path): ?>
		<li>[<?php $this->o($path['action'])?>] <a href="#<?php $this->o(str_replace('/trunk/', '', $path['path']))?>"><?php $this->o($path['path'])?></a></li>
	<?php endforeach; ?>
	</ul>
</p>
<div class="clear"></div>
<?php endforeach; ?>

<h3>File diffs</h3>

<?php $i = 0; foreach ($diff->diffs as $filediff): ?>
<div style="margin-bottom: 1em;">
 	<span class="file-diff-name"><a name="<?php $this->o($filediff->name)?>"><?php $this->o($filediff->name)?></a> (<a href="javascript:void(0);" onclick="$('#diff-<?php echo $i?>').toggle()">Show</a>)</span> 
	<div id="diff-<?php echo $i;?>" style="display: none; padding: 1em;">
	<table class="code-review-table">
		<thead>
			<tr>
			<th></th>
			<th></th>
			<th>Revision <?php $this->o($this->model->previousrevision)?></th>
			<th></th>
			<th></th>
			<th>Revision <?php $this->o($this->model->revision)?></th>
			</tr>
		</thead>
		<tbody>
	<?php 
	$oldStartingLine = $filediff->oldStartLine;
	$newStartingLine = $filediff->newStartLine;
	foreach ($filediff->diffOps as $diffOp) {
		$length = max(count($diffOp->orig), count($diffOp->final));
		$class = str_replace('Text_Diff_Op_', 'diff-', get_class($diffOp));
		
		for ($j = 0; $j < $length; $j++) {
			?>
			<tr>
			<?php if (isset($diffOp->orig[$j])): 
    			$lineName = $this->escape($filediff->name).'-original-line-'.$oldStartingLine; ?>
				<td width="3%">
				<a class="diff-line-num" name="<?php echo $lineName?>" href="#"><?php echo $oldStartingLine++; ?></a>
				</td>
				<td width="2%">
				<?php if (isset($this->comments[$lineName])): ?>
				(<a class="diff-comment-link" href="#<?php echo $lineName?>-comment"><?php echo count($this->comments[$lineName])?></a>)
				<?php endif; ?>
				</td>
				<td width="45%" class="<?php $this->o($class)?>"><?php echo str_replace(' ', '&nbsp;', str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;",  $this->escape($diffOp->orig[$j])));?></td>
			<?php else: ?>
				<td width="3%"></td>
				<td width="2%"></td>
				<td width="45%"></td>
			<?php endif; ?>

			<?php if (isset($diffOp->final[$j])):
                $lineName = $this->escape($filediff->name).'-final-line-'.$newStartingLine; ?>
				<td width="3%"><a class="diff-line-num" name="<?php echo $lineName?>" href="#"><?php echo $newStartingLine++; ?></a>
				</td>
				<td width="2%">
				<?php if (isset($this->comments[$lineName])): ?>
				(<a class="diff-comment-link" href="#<?php echo $lineName?>-comment"><?php echo count($this->comments[$lineName])?></a>)
				<?php endif; ?>
				</td>
				<td width="45%" class="<?php $this->o($class)?>"><?php echo str_replace(' ', '&nbsp;', str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;",  $this->escape($diffOp->final[$j])));?></td>
			<?php else: ?>
				<td width="3%"></td>
				<td width="2%"></td>
				<td width="45%"></td>
			<?php endif; ?>
			</tr>
			<?php
		}
	}
 ?>	
 	</tbody>
	</table>
	</div>
</div> 
<?php $i++; endforeach; ?>


<?php foreach ($this->comments as $name => $commentCluster): ?>
	<h3><a name="<?php $this->o($name)?>-comment"><?php $this->o($name)?></a></h3>
	
	<?php foreach ($commentCluster as $comment): ?>
	<div class="note">
        <div class="note-header">
			<span class="note-by">By <?php $this->o($comment->userid.' on '.$comment->created)?></span>
            <?php $this->o($comment->title)?>
        </div>
        <div class="note-body"><?php $this->bbCode($comment->note);?></div>
    </div>
    <?php endforeach; ?>

<?php endforeach; ?>

<div id="add-comment-container" style="padding: 10px; display: none; border: 1px solid #333; background-color: #efefef; position: absolute; width: 400px;">
	[<a href="#" onclick="$('#add-comment-container').hide(); return false;">X</a>]
	<form method="post" action="<?php echo build_url('codereview','addcomment')?>">
		<input type="hidden" name="id" value="<?php echo $this->model->id?>" />
		<input type="hidden" name="line" value="" id="line-field" />
		<p>Comment</p>
		<textarea rows="6" style="width: 375px;" name="comment"></textarea>
		<p>
		<input type="submit" value="Add Comment" />
		</p>
	</form>
</div>

<script type="text/javascript">
	$('.diff-line-num').click(function() {
		$('#line-field').val($(this).attr('name'));
		
		// figure out where to put it
		var loc = $(this).position();

		$('#add-comment-container').css("top", loc.top+"px");
		$('#add-comment-container').css("left", (loc.left + 100)+"px");
		
		$('#add-comment-container').show();
		return false;
	});
</script>

<div class="clear"><p></p><p></p></div>
<a href="<?php echo build_url('codereview', 'edit', array('id'=>$this->model->id));?>" class="abutton">Edit</a>

    <?php $deleteStyle = isset($this->existingWatch) ? 'inline' : 'none' ?>
    <?php $addStyle = $deleteStyle == 'inline' ? 'none' : 'inline' ?>
    
    <a class="abutton" style="display: <?php echo $deleteStyle?>;" id="delete-watch" href="#" onclick="$.get('<?php echo build_url('note', 'deletewatch')?>', {id:'<?php echo $this->model->id?>', type:'<?php echo get_class($this->model)?>'}, function() {$('#delete-watch').hide();$('#add-watch').show(); }); return false; ">Remove Watch</a>
	<a class="abutton" style="display: <?php echo $addStyle?>;" id="add-watch" href="#" onclick="$.get('<?php echo build_url('note', 'addwatch')?>', {id:'<?php echo $this->model->id?>', type:'<?php echo get_class($this->model)?>'}, function() {$('#add-watch').hide();$('#delete-watch').show(); }); return false; ">Add Watch</a>
<?php endif; ?>