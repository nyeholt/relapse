<?php

class Helper_AddNote
{
	public function AddNote($subject, $id, $type, $iconClass='', $linkClass='abutton')
	{
	    $subject = str_replace("'", "\'", $subject);
		?>
<a class="<?php echo $linkClass?>" title="Add Note" href="#" onclick="addNote('<?php echo $subject;?>', <?php echo $id; ?>, '<?php echo $type?>'); return false;">Add Note</a>
		<?php
	}
}
?>