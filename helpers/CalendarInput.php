<?php

class Helper_CalendarInput extends NovemberHelper
{
	public function CalendarInput($id, $label, $forField, $default="", $showtime=false, $extra="")
	{
	    $format = 'Y-m-d';

		$dateValue = $this->view->model->$forField ? date($format, strtotime($this->view->model->$forField)) : $default;
		$timeValue = false;

	    if ($showtime) {
	        $timeValue = $this->view->model->$forField ? date('H:i', strtotime($this->view->model->$forField)) : $default;
	    }
		
	    ?>
	    <p>
	    <label for="<?php echo $id?>"><?php $this->view->o($label)?>:</label>
	    <input class="input" readonly="readonly" type="text" name="<?php echo $forField?>"
	        id="<?php echo $id?>" value="<?php echo $dateValue ?>" <?php echo $extra?>/>
		<?php if ($showtime): ?>
		<input class="time-input" readonly="readonly" type="text" name="<?php echo $forField?>-time" id="<?php echo $id?>-time" value="<?php echo $timeValue ?>" />
		<?php endif; ?>
	    </p>
	    <?php 
	    $options = new stdClass();
	    if ($showtime) {
	        $options->showTime = true;
	    }
	    $this->view->calendar($id, $options);
		$this->view->timePicker($id.'-time');
	}
}

?>