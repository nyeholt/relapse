<?php

class Helper_FilePicker extends NovemberHelper 
{
	public function FilePicker($label, $forField)
	{
		?>
		
	<p>
    <label for="<?php echo $forField?>"><?php $this->view->o($label)?>:</label>
    <input readonly="readonly" class="input file-picker-input" type="text" name="<?php echo $forField?>"
        id="<?php echo $forField?>" value="<?php echo $this->view->model->$forField?>" />
    <input value="Choose..." type="button" class="file-picker-button" onclick="popup('<?php echo build_url('file', 'index', array('picker'=>$forField), true, 'default')?>', 'filepicker_<?php echo $forField?>', '600', '400')" />
    <div style="height: 110px;">
	    <img id="<?php echo $forField?>-thumbnail"  />
    </div>
    <?php if ($this->view->model->$forField): ?>
		<script type="text/javascript">
			setPickerThumb($('#<?php echo $forField?>-thumbnail'), '<?php echo $this->view->model->$forField?>', '<?php echo build_url('file', 'viewthumbnail', '', true, 'default');?>');
		</script>
    <?php endif; ?>
	</p>
	
	<?php 
	}
}

?>