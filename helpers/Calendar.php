<?php

class Helper_Calendar extends NovemberHelper 
{
	public function Calendar($forField, $options=null)
	{
		if (!is_object($options)) {
			$options = new stdClass();
			$options->showTime = false;
		}

		if (!isset($options->dateFormat)) {
			$options->dateFormat = "yy-mm-dd";
		}

		$options->showAnim = 'fadeIn';

		$options = Zend_Json::encode($options);

		echo '<script type="text/javascript">';
		echo "$().ready(function() { $('#$forField').datepicker($options); });";
		echo '</script>';

	}
}

?>