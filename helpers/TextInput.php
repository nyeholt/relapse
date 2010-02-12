<?php

class Helper_TextInput extends NovemberHelper
{
	/**
	 * Creates an input field or textarea
	 * $label - Label in front of input
	 * $forField - name of model field
	 * $multiple - if true: textarea, false: input
	 * $extra - extra html attributes
	 * $defaultValue - will be put in value field if model has not value
	 * $postFixLabel - only on multiple=false will be printed directly after the input field (for % signs etc.)
	 */
    public function TextInput($label, $forField, $multiple=false, $extra='', $defaultValue='', $postFixLabel='')
    {
        $value = isset($this->view->model->$forField) ? $this->view->model->$forField : $defaultValue;
        
        if ($multiple) {
        ?>
    <p>
    <label for="<?php echo $forField?>"><?php $this->view->o($label)?>:</label>
    <textarea class="input" <?php echo $extra?> name="<?php echo $forField?>" id="<?php echo $forField?>"><?php $this->view->o($value, false) ?></textarea>
    </p>
        
        <?php    
        } else {
        ?>
    <p>
    <label for="<?php echo $forField?>"><?php $this->view->o($label)?>:</label>
    <input class="input" type="text" name="<?php echo $forField?>"
        id="<?php echo $forField?>" value="<?php $this->view->o($value)?>" <?php echo $extra?>/>&nbsp;<?php echo $postFixLabel?>
    </p>
        
        <?php
        }
    }
}
?>