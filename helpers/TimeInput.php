<?php

/**
 * Shortcut to get the current user
 *
 */
class Helper_TimeInput extends NovemberHelper
{
	/**
	 * Get the current user
	 *
	 * @return  NovemberUser
	 */
	public function TimeInput($label, $forField, $forForm)
	{
	    $value = isset($this->view->model->$forField) ? $this->view->model->$forField : '';
	    $hours = mb_substr($value, 0, mb_strpos($value, ':'));
        $mins = mb_substr($value, mb_strpos($value, ':') + 1);
        
        ?>
        
        <script type="text/javascript">
		    $(document).ready(function(){
		        $('.<?php echo $forForm?>').submit(function() {
		
		            var hour = $('#<?php echo $forField ?>hour').val();
		            var minute = $('#<?php echo $forField ?>minute').val();
		            if (hour.length > 0) {
		            	if (minute.length == 0) {
		            		minute = '00';
		            	}
		            	if (minute.length == 1) {
		            		minute = '0'+minute;
		            	}
		                $('#<?php echo $forField ?>').val(hour + ":" + minute);
		            }

		            return true;
		        });
		    });
		</script>

		<input type="hidden" id="<?php echo $forField ?>" name="<?php echo $forField ?>" value="<?php $this->view->o($value);?>" />
        
        <p>
        <label><?php $this->view->o($label)?>:</label>
        <select id="<?php echo $forField;?>hour" class="narrow">
        	<option></option>
        	<?php for ($i = 0; $i < 24; $i++) {
        	    $thisHour = (string) $i;
        	    if (mb_strlen($thisHour) == 1) $thisHour = '0'.$thisHour;

        		$selected = $hours === $thisHour; 
        		echo "<option value='$thisHour' ".($selected ? "selected='selected'" : '').">$thisHour</option>\n";
        	}?>
        </select> : 
        
        <select id="<?php echo $forField;?>minute" class="narrow">
	        <option></option>
        	<?php for ($i = 0; $i < 60; $i+=5) {
        	    $thisMin = (string) $i;
        	    if (mb_strlen($thisMin) == 1) $thisMin = '0'.$thisMin;

        		$selected = $mins === $thisMin; 
        		
        		echo "<option value='$thisMin' ".($selected ? "selected='selected'" : '').">$thisMin</option>\n";
        	}?>
        </select>
        </p>
        <?php
	}
}

?>