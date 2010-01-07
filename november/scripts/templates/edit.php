<form method="post" action="<?php php(); ?> echo build_url('<?php echo $modelType?>', 'save');<?php unphp();?>">
<?php php(); ?> if ($this->model->id): <?php unphp(); ?>
    <input type="hidden" value="<?php php(); ?> echo $this->model->id<?php unphp(); ?>" name="id" />
<?php php(); ?> endif; <?php unphp(); ?>
    <p>
    <label for="title"><?php echo $name; ?> title:</label>
    <input class="input" type="text" name="title" size="40"
        id="title" value="<?php php(); ?> echo $this->model->title<?php unphp(); ?>" />
    </p>
    
    <p>
    	<input type="submit" value="Save" accesskey="s"  class="wymupdate"/>
    	<input type="button" value="Done" onclick="location.href='<?php php(); ?> echo build_url('<?php echo $modelType?>', 'list');<?php unphp();?>'" />
    </p>
</form>