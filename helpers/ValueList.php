<?php

class Helper_ValueList extends NovemberHelper 
{
	public function ValueList($label, $forField, $forForm, $options, $defaultValue='')
	{
		
	    ?>
	    
<script type="text/javascript">
    $(document).ready(function(){
        $('.<?php echo $forForm?>').submit(function() {

            var entered = $('#<?php echo $forField ?>text').val();
            var selected = $('#<?php echo $forField ?>select').val();
            if (entered.length > 0) {
                $('#<?php echo $forField ?>').val(entered);
            } else if (selected.length) {
                $('#<?php echo $forField ?>').val(selected);
            }
            
            return true;
        });
    });
</script>
	    
	<p>
    <label for="<?php echo $forField ?>text"><?php echo $label?>:</label>
    <input type="hidden" id="<?php echo $forField ?>" name="<?php echo $forField ?>" value="<?php $this->view->o($this->view->model->$forField);?>" />
    <input class="input valuelisttext" type="text" value="" id="<?php echo $forField ?>text" size="8" />
    <select id="<?php echo $forField ?>select" class="short valuelistselect">
        <?php foreach ($options as $option): ?>
            <option value="<?php $this->view->o($option)?>" <?php echo ($option == $this->view->model->$forField || $option == $defaultValue) ? 'selected="selected"' : ''?>><?php $this->view->o($option)?></option>
        <?php endforeach; ?>
    </select>
    </p>
	    
	    <?php
	}
}

?>