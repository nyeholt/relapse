<?php

/**
 * Shortcut to get the current user
 *
 */
class Helper_U
{
	/**
	 * Get the current user
	 *
	 * @return  NovemberUser
	 */
	public function U()
	{
		return za()->getUser();
	}
}

?>