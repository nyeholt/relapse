<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_Csv extends NovemberHelper
{
	public function Csv($text, $wrap = true)
	{
		if ($wrap) {
			$text = wordwrap($text);
		}
	    $text = str_replace('"', '""', $text);
	    
		echo '"'.$text.'"';
	}
}

?>