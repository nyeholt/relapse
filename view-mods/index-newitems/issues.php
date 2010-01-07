<h4>Issues</h4>
<div id="new-issue-list">
Loading...
</div>

<script type="text/javascript">
$(document).ready(function() {
    $("#new-issue-list").load('<?php echo build_url('issue', 'issuelist', array('type'=>'new'));?>');
});

</script>