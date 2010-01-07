<?php

/**
 * Shortcut to calculate seconds into hours
 *
 */
class Helper_ElapsedTime
{
	/**
	 * Get the amount of elapsed time for a given number of seconds.
	 *
	 * @return  NovemberUser
	 */
	public function ElapsedTime($total, $formatted = false)
	{
	    if (!$total) return '';
	    // holds formatted string
	    $hms = '';
	    
	    // there are 3600 seconds in an hour, so if we
	    // divide total seconds by 3600 and throw away
	    // the remainder, we've got the number of hours
	    $hours = intval(intval($total) / 3600); 
	
	    // add to $hms, with a leading 0 if asked for
	    $hms .= $hours. ':';

	    // dividing the total seconds by 60 will give us
	    // the number of minutes, but we're interested in 
	    // minutes past the hour: to get that, we need to 
	    // divide by 60 again and keep the remainder
	    $minutes = intval(($total / 60) % 60); 

	    $minutesAsHours = $minutes / 60; 
	    
	    // then add to $hms (with a leading 0 if needed)
	    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT);

	    if ($formatted) {
	    	return $hms;
	    }
	    
		return sprintf('%.2f', $hours + $minutesAsHours);
	}
	
}

?>