<h2><?php echo count($this->results)?> results for "<?php $this->o($this->query); ?>"</h2>
<br/>
<ul>
<?php
$page = ifset($_GET, 'page', 1);
$start = ($page - 1) * $this->perPage;
$finish = $page * $this->perPage;

for ($i = $start; isset($this->results[$i]) && $i < $finish; $i++) {
    $result = $this->results[$i];
    $this->showSearchResult($result);
}
?>
</ul>
<p>
<?php $this->pager(count($this->results), $this->perPage, 'page', array('query'=>$this->query)); ?>
</p>
