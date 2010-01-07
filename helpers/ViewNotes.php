<?php

class Helper_ViewNotes
{
	public function ViewNotes($id, $type, $iconClass='', $linkClass='abutton')
	{
		?>
		
<a class="<?php echo $linkClass?>" title="View Notes" href="#" onclick="viewNotes(<?php echo $id; ?>, '<?php echo $type?>'); return false;">View Notes</a>
		<?php
	}
}
?>