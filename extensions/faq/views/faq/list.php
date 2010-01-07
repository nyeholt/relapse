<h2>
FAQs
</h2>

<div class="tag-listing">
<?php foreach ($this->tags as $tag): ?>
<a href="<?php echo build_url('faq', 'list', array('tag'=>$tag['tag']))?>"><?php $this->o($tag['tag'].' ('.$tag['frequency'].')') ?></a> 
<?php endforeach; ?>
<p>
<a href="<?php echo build_url('tag', 'index', array('type'=>'faq'))?>">Show all</a>
</p>
</div>
<div id="tag-search">
<h4>Search FAQs</h4>
<form method="get" action="<?php echo build_url('faq')?>">
<input type="text" name="query" size="40" />
<input type="submit" value="Search" />
</form>
</div>

<div class="clear"></div>

<table class="item-table" cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th width="50%">Title</th>
        <th>Author</th>
        <th>Created</th>
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php $index=0; foreach ($this->items as $item): ?>
    <tr class="<?php echo $index++ % 2 == 0 ? 'even' : 'odd'?>">
        <td>
        <p><a href="<?php echo build_url('faq', 'view', array('id'=>$item->id))?>"><?php $this->o($this->ellipsis($item->title, 40));?></a></p>
        <p class="faq-summary"><?php $this->o($item->description) ?></p>
        </td>
        <td><?php $this->o($this->ellipsis($item->author, 40));?></td>
        <td><?php $this->o(date('Y-m-d', strtotime($item->authored)));?></td>
        <td>
        	<a href="<?php echo build_url('faq', 'view', array('id'=>$item->id))?>"><img src="<?php echo resource('images/eye.png')?>" /></a>
            <a onclick="if (!confirm('Are you sure?')) return false; location.href='<?php echo build_url('faq', 'delete', array('id'=>$item->id))?>'; return false;" href="#"><img src="<?php echo resource('images/delete.png')?>" /></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php if ($this->listSize) $this->pager($this->totalCount, $this->listSize, $this->pagerName); ?>
<p>
<a href="<?php echo build_url('faq', 'edit')?>" class="abutton">Add FAQ</a>
</p>