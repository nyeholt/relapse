<?php

/**
 * Shortcut to get the current user
 *
 */
class Helper_Ellipsis
{
	/**
	 * Get the current user
	 *
	 * @return  NovemberUser
	 */
	public function ellipsis($val, $length=21, $dynamic = false)
	{
	    if (strlen($val) >= $length) {
	    	if ($dynamic) {
		    	$text = '<span>';
		    	$text .= '<span class="ellipses-dynamic-short">'.substr($val, 0, $length-4).'<a href="#" onclick="$(this).parent().hide().parent().find(\'.ellipses-dynamic-full\').show(); return false;">...</a></span>';
		    	$text .= '<span style="display: none" class="ellipses-dynamic-full">'.$val.'<a href="#" onclick="$(this).parent().hide().parent().find(\'.ellipses-dynamic-short\').show(); return false;">&laquo;</a></span>';
		    	$text .= '</span>';
		    	
		    	// add in the logic for displaying the whole thing instead of just the small bit
		    	
		        return $text;
	    	} else {
	        	return substr($val, 0, $length-4).'...';
	    	}
	    }
	    return $val;
	}
}

?>