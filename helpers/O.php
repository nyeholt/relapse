<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_O extends NovemberHelper
{
	public function O($text, $newlines = true)
	{
		$val = $this->view->escape($text);
		if ($newlines) {
		    $val = nl2br($val);
		}
		
		echo $val;
	}
}

?>