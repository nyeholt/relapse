<?php

class Helper_YesNoInput extends NovemberHelper 
{
    function YesNoInput($label, $forField)
    {
        ?>
        
    <p>
    <label for="<?php echo $forField ?>text"><?php echo $label?>:</label>
	<span class="yesNoInput">
	Yes <input type="radio" name="<?php echo $forField?>" value="1" <?php echo $this->view->model->$forField ? 'checked="checked"' : ''?> />
	No <input type="radio" name="<?php echo $forField?>" value="0" <?php echo !$this->view->model->$forField ? 'checked="checked"' : ''?> />
	</span>
    </p>
        
        <?php
    }
}
?>