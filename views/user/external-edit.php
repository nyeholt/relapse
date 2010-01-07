<h2>Editing <?php $this->o($this->model->username) ?> </h2>
<form method="post" action="<?php echo build_url('user', 'edit', array('id' => $this->model->id));?>">

<p><label for="password">Password</label><input id="password" type="password" name="password" size="20" maxlength="40" /></p>
<p><label for="confirm">Confirm Password</label><input id="confirm" type="password" name="confirm" size="20" maxlength="40" /></p>
<p>
    <input type="submit" class="abutton" name="submit" value="Update" accesskey="s" />
</p>
</form>
