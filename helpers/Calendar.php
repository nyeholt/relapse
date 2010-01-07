<?php

class Helper_Calendar extends NovemberHelper 
{
	public function Calendar($forField, $options="")
	{

		$this->view->addHeadItem('JSCalendar', $this->view->script(resource('jscalendar/calendar_stripped.js'), true)); 
		$this->view->addHeadItem('JSCalendarLang', $this->view->script(resource('jscalendar/lang/calendar-en.js'), true));
		$this->view->addHeadItem('JSCalendarSetup', $this->view->script(resource('jscalendar/calendar-setup.js'), true));
		$this->view->addHeadItem('JSCalendarCSS', $this->view->style(resource('jscalendar/calendar-system.css'), true));

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