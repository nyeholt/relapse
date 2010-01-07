<?php

class Helper_PriceInput extends NovemberHelper 
{
    public function PriceInput($label, $forField, $extra='')
    {
        ?>
    <p>
    <label for="<?php echo $forField?>"><?php $this->view->o($label)?>:</label>
    $ <input class="input price" type="text" name="<?php echo $forField?>"
        id="<?php echo $forField?>" value="<?php $this->view->o($this->view->model->$forField)?>" <?php echo $extra?>/>
    </p>
        
        <?php
    }
}
?>