
<form method="post" action="<?php echo build_url('user', 'register');?>">

<p><label for="username">Username</label><input id="username" type="text" name="username" size="20" maxlength="40" /></p>
<p><label for="email">Email Address</label><input id="email" type="text" name="email" size="20" maxlength="40" /></p>
<p><label for="password">Password</label><input id="password" type="password" name="password" size="20" maxlength="40" /></p>
<p><label for="confirm">Confirm Password</label><input id="confirm" type="password" name="confirm" size="20" maxlength="40" /></p>
<p><input type="submit" class="abutton" name="submit" value="Register" /></p>
</form>