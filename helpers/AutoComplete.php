<?php

class Helper_AutoComplete extends NovemberHelper
{
    public function AutoComplete($label, $forField, $url, $extra='')
    {
        
        ?>
    <p>
    <label for="<?php echo $forField?>"><?php $this->view->o($label)?>:</label>
    <input class="input" type="text" name="<?php echo $forField?>"
        id="<?php echo $forField?>" value="<?php $this->view->o($this->view->model->$forField)?>" <?php echo $extra?>/>
    </p>
        <script type="text/javascript">
        $().ready(function() {
	        $('#<?php echo $forField?>').autocomplete('<?php echo $url?>', { autoFill:false, minChars:1, matchSubset:true, matchContains:false, cacheLength:10, selectOnly:true, mode:"multiple",multipleSeparator:"," });
        });
        </script>
        <?php
    }
}
?>