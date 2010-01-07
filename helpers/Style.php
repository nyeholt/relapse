<?php

class Helper_Style
{
	public function Style($path, $return=false)
	{
	    $str = '<link rel="stylesheet" type="text/css" href="'.$path.'"></link>';
	    	    
	    if ($return) {
	        return $str;
	    } else {
	        echo $str;
	    }
	}
}

?>