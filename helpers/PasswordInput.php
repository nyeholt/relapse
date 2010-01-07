<?php

class Helper_PasswordInput extends NovemberHelper 
{
    public function PasswordInput($label, $forField, $extra='')
    {
        ?>
    <p>
    <label for="<?php echo $forField?>"><?php $this->view->o($label)?>:</label>
    <input class="input" type="password" name="<?php echo $forField?>"
        id="<?php echo $forField?>" <?php echo $extra?>/>
    </p>
        <?php
    }
}
?>