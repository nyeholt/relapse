<div class="action-list-item">
    <h4>Latest Notes</h4>
    <div id="latest-notes-listing">
    Loading...
    </div>
</div>


<script type="text/javascript">
$(document).ready(function() {
    $("#latest-notes-listing").load('<?php echo build_url('note', 'latestnotes');?>');
});
</script>