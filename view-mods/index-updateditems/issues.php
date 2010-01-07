<h4>Issues</h4>
<div id="updated-issue-list">
Loading...
</div>


<script type="text/javascript">
$(document).ready(function() {
    $("#updated-issue-list").load('<?php echo build_url('issue', 'issuelist', array('type'=>'updated'));?>');
});

</script>