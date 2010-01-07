<h2>Editing leave for <?php $this->o($this->model->username); ?></h2>
<form method="post" action="<?php echo build_url('leave', 'save');?>">
<input type="hidden" name="id" value="<?php echo $this->model->id ?>" />

	<p>
	    <label for="days">Number of days to add (negative to subtract)</label>
	    <input id="days" type="text" value="<?php echo $this->model->days?>" name="days" size="8" maxlength="40" />
	</p>
	
	<p>
	    <input type="submit" class="abutton" name="submit" value="Update" />
	</p>

</form>