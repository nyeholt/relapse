<ol id="ordered-feature-list">
    <?php foreach ($this->features as $feature): ?>
        <li class="ordered-feature" id="<?php echo $feature->id?>">
            <?php $this->o($feature->title);?>
        </li>
    <?php endforeach;?>
</ol>

<form action="<?php echo build_url('feature', 'saveorder');?>" method="post" onsubmit="return saveHash();">
    <input type="hidden" id="ordered-ids" name="ids" />
    <input type="submit" class="abutton" value="Save" />
</form>
<script type="text/javascript">
$('#ordered-feature-list').Sortable (
    {
        accept: 'ordered-feature',
        helperclass: 'sortHelper',
        activeclass : 	'sortableactive',
		hoverclass : 	'sortablehover',
		onChange : function(ser)
		{
		},
		onStart : function()
		{
			
		},
		onStop : function()
		{
			// $.iAutoscroller.stop();
			//serial = $.SortSerialize('ordered-feature-list');
            //alert(serial.hash);
		}
    }
);

function saveHash()
{
    serial = $.SortSerialize('ordered-feature-list');
    $('#ordered-ids').val(serial.o["ordered-feature-list"].toString());
    return true;
}
</script>