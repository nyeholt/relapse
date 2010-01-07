<span style="float: right">[<a href="#" onclick="$(this).parent().parent().hide(); return false;">X</a>]</span>
<script type="text/javascript">
$(document).ready(function(){
	$("#thetree").treeview({
		url: "<?php echo build_url('tree', 'data', array('id' => $this->id))?>"
	})
});
</script>

<ul id="thetree">
</ul>
