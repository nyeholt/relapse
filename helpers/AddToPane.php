<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_AddToPane extends NovemberHelper
{
	public function AddToPane($url, $text, $title = null, $pane = 'CenterPane')
	{
		$title = $this->view->escape($title);
		echo '<a title="'.$title.'" class="targeted" href="'.$url.'" target="'.$pane.'">'.$text.'</a>';
	}
}

?>