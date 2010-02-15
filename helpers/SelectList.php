<?php

class Helper_SelectList extends NovemberHelper 
{
	public function SelectList($label, $forField, $options, $default='', $valueField='id', $displayField='title', $multiple=false, $empty=false, $extra='')
	{
	    $name = $multiple ? $forField.'[]' : $forField;
		?> 
	<p style="clear: both;">
    <label for="<?php echo $forField ?>"><?php echo $label?>:</label>
    
    <select id="<?php echo $forField ?>" name="<?php echo $name?>" <?php echo $multiple ? 'multiple="multiple" size="'.$multiple.'"' : ''?> <?php echo $extra?>>
    	<?php 
    	if ($empty) {
    	    echo '<option value=""></option>'."\n";
    	}
    	
        $sel = isset($this->view->model->$forField) && !is_null($this->view->model->$forField) ? $this->view->model->$forField: $default;

        foreach ($options as $option): 
            
            $displayValue = $option;
            $fieldValue = $option;
            if (is_array($option)) {
                // The display field might be a concat of many fields
                $displayValue = "";
                if (is_array($displayField)) {
                    foreach ($displayField as $dField) {
                        $displayValue += $option[$dField].' ';
                    }
                } else {
                    $displayValue = $option[$displayField];
                }
                
                $fieldValue = $option[$valueField];   
            } else if (is_object($option)) {
                $displayValue = "";
                if (is_array($displayField)) {
                    foreach ($displayField as $dField) {
                        $displayValue .= $option->$dField.' ';
                    }
                } else {
                    $displayValue = $option->$displayField;
                }

                $fieldValue = $option->$valueField;
            }

            $selected = false;
            if ($multiple && is_array($sel)) {
                 // expect the value to be an array
                $selected = in_array($fieldValue, $sel);
            } else {
                $selected = $fieldValue == $sel;
            }
        ?>
            <option value="<?php $this->view->o($fieldValue)?>" <?php echo $selected ? 'selected="selected"' : ''?>><?php $this->view->o($displayValue)?></option>
        <?php endforeach; ?>
    </select>
    </p>
	    
	    <?php
	}
}

?>