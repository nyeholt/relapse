<?php

class Helper_Errors
{
	public function Errors(CompositeView $view)
	{
		if (count($view->getErrors())) {
			echo '<ul class="november-error">';
			foreach ($view->getErrors() as $id => $error) {
				echo '<li>'.$error.'</li>';
			}
			echo '</ul>';
		}
	}
}

?>