<?php

class Helper_Calendar extends NovemberHelper 
{
	public function Calendar($forField, $options="")
	{
		// Create a basic calendar
		if ($options == '') {
			$options = 'ifFormat : "%Y-%m-%d %H:%M", showsTime:true, timeFormat:"24",singleClick:false';
		}
		
		$options = 'inputField : "'.$forField.'", '.$options;
		rtrim($options, ',');

		echo '<script type="text/javascript">';
		echo '$().ready(function() { Calendar.setup({'.$options.'}); });';
		echo '</script>';

	}
}

?>