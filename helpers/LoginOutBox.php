<?php

include_once dirname(__FILE__).'/O.php';

class Helper_LoginOutBox extends NovemberHelper 
{
	public function LoginOutBox($adminLinks = false)
	{
		$user = za()->getUser();
		if ($user instanceof GuestUser) {
		?>
			<a href="<?php echo build_url('user', 'login'); ?>" title="Login">Login</a>
			<!--<a href="<?php echo build_url('user', 'register'); ?>" title="Register">Register</a>-->
		<?php
		} else {
		?>
			<div id="loggedin">
				<p>Welcome, <?php $this->view->o($user->getUsername()); ?>
				&nbsp;&nbsp;&nbsp;

				<a href="<?php echo build_url('index'); ?>" title="Home">Home</a>
				<?php if ($user->role == User::ROLE_EXTERNAL): ?>
					&nbsp;|&nbsp;
					<a href="<?php echo build_url('user', 'edit'); ?>">Change Settings</a>
					&nbsp;|&nbsp;
					<a href="<?php echo build_url('contact', 'edit', array('id'=>$user->contactid)); ?>">Change Details</a>
				<?php endif; ?>

				<?php if ($user->hasRole(User::ROLE_USER)): ?>
					&nbsp;|&nbsp;
					<a href="<?php echo build_url('user', 'edit'); ?>">Details</a>
					&nbsp;|&nbsp;
					<a href="<?php echo build_url('timesheet', 'index', array('username'=>$user->getUsername())); ?>">View Time</a>
				<?php endif; ?>

				<?php if (za()->getUser()->hasRole(User::ROLE_POWER)): ?>
					| <a href="<?php echo build_url('timesheet', 'filterSummary'); ?>">Summary Report</a> | 
					<a href="<?php echo build_url('leave', 'list'); ?>">Leave</a> |
					<a href="<?php echo build_url('admin');?>">Admin</a>
				<?php endif; ?>
				| <a href="<?php echo build_url('user', 'logout'); ?>" title="Logout">Logout</a>

			</div>
		<?php
		}
	}
}

?>