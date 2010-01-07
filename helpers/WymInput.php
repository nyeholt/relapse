<?php
class Helper_WymInput extends NovemberHelper
{
    public function WymInput($label, $forField, $options=array())
    {
    	$this->view->addHeadItem('WymCSS', $this->view->style(resource('wymeditor/skins/default/screen.css'), true));
    	$this->view->addHeadItem('WymJS', $this->view->script(resource('wymeditor/jquery.wymeditor.js'), true));
    	
        ?>
        
        <p>
	    <label for="<?php echo $forField?>"><?php echo $label?></label>
	        <textarea class="wymeditor" id="<?php echo $forField?>" name="<?php echo $forField?>"><?php echo $this->view->model->$forField != null ? $this->view->o($this->view->model->$forField, false) : $this->view->o('<p>&nbsp;</p>', false)?></textarea>
	    </p>
        <script type="text/javascript">
        
	        $(document).ready(function() {
	            $('#<?php echo $forField?>').wymeditor({
	            	<?php foreach ($options as $k => $v) {
	            	    echo $k.':'.$v.',';
	            	} ?>
	            	
	                jQueryPath: '<?php echo resource('jquery-1.2.2-b.js')?>'
	            });
	        });
	    </script>
        <?php 
    }
}
?>