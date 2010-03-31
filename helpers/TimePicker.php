<?php

class Helper_TimePicker extends NovemberHelper
{
	public function TimePicker($forField, $options=null)
	{
		if (!is_object($options)) {
			$options = new stdClass();
		}

		$options = Zend_Json::encode($options);

		echo '<script type="text/javascript">';
		echo "$().ready(function() { $('#$forField').timepickr($options); });";
		echo '</script>';
	}
}

?>