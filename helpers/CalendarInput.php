<?php

class Helper_CalendarInput extends NovemberHelper
{
	public function CalendarInput($id, $label, $forField, $default="", $showtime=false, $extra="")
	{
	    $format = 'Y-m-d';

	    if ($showtime) {
	        $format = 'Y-m-d H:i:s';
	    }
	    ?>
	    <p>
	    <label for="<?php echo $id?>"><?php $this->view->o($label)?>:</label>
	    <input class="input" readonly="readonly" type="text" name="<?php echo $forField?>"
	        id="<?php echo $id?>" value="<?php echo $this->view->model->$forField ? date($format, strtotime($this->view->model->$forField)) : $default?>" <?php echo $extra?>/>
	    </p>
	    <?php 
	    $options = new stdClass();
	    if ($showtime) {
	        $options->showTime = true;
	    }
	    $this->view->calendar($id, $options);
	}
}

?>