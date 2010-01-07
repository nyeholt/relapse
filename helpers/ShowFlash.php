<?php

class Helper_ShowFlash
{
	public function ShowFlash(CompositeView $view)
	{
	    $flash = $view->flash();
	    if ($flash == null) {
	        return;
	    }
	    echo '<div class="november-flash">';
	    if (is_array($flash)) {
	        $this->outputArray($flash);
	    } else {
		  echo $flash;
	    }
	    echo '</div>';
	}
	
	private function outputArray($a)
	{
	    echo '<ul>';
	    foreach ($a as $key => $value) {
	        echo '<li>';
	        if (is_string($key)) {
	            echo '<strong>'.$key.'</strong>';
	        }
	        if (is_array($value)) {
	            $this->outputArray($value);
	        } else {
	            echo $value;
	        }
	        echo '</li>';
	    }
	    echo '</ul>';
	}
}

?>