<?php

class Helper_Script
{
	public function Script($path, $return=false)
	{
	    $str = '<script type="text/javascript" src="'.$path.'"></script>';
	    if ($return) {
	        return $str;
	    } else {
	        echo $str;
	    }
	}
}

?>