<?php

class Helper_YesNoInput extends NovemberHelper 
{
    function YesNoInput($label, $forField)
    {
        ?>

	<div class="yesNoInput">
    <label for="<?php echo $forField ?>text"><?php echo $label?>:</label>
	<span class="yesNoYes">Yes </span><input type="radio" name="<?php echo $forField?>" value="1" <?php echo $this->view->model->$forField ? 'checked="checked"' : ''?> />
	<span class="yesNoNo">No </span><input type="radio" name="<?php echo $forField?>" value="0" <?php echo !$this->view->model->$forField ? 'checked="checked"' : ''?> />
	</div>
    <?php
    }
}
?>