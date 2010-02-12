
<div class="std">
<h1>Login</h1>
<?php if (za()->getUser()->getId()): ?>
<div id="login-warning">
<p>You are logged in as <?php $this->o(za()->getUser()->username); ?>, but do not have access to this page</p>
<p>If you think this is an error, please <a href="mailto:<?php echo za()->getConfig('from_email')?>">email us</a>.</p>
</div>
<?php endif; ?>
<form class="data-form" method="post" action="<?php echo current_url();?>">
<p>Your browser <strong>must</strong> allow cookies in order for this login process to work and for your account to remain logged in.<br /><br /></p>
<div>
	<p>
		<label for="email">Username</label>
		<?php echo $this->formText("username", "", array("maxlength"=>40,"id"=>"username")); ?>
	</p>
	<p>
		<label for="password">Password</label>
		<?php echo $this->formPassword("password", "", array("maxlength"=>40,"id"=>"password")); ?>
		<br />
	
		Have you forgotten your password?&nbsp;&nbsp;
		<a href="<?php echo build_url('user', 'password'); ?>" title="Forgot Password?">Click here to request a new one.</a>
	</p>
	<p><?php echo $this->formSubmit("submit", "Login"); ?></p>

</div>
</form>
</div>