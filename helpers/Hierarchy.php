<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_Hierarchy extends NovemberHelper
{
	public function Hierarchy($items, $separator = '&raquo;', $class='hierarchy-breadcrumb')
	{
		if ($class) {
			echo '<div class="hierarchy-breadcrumb">';	
		}
		
		$sep = '';
		foreach ($items as $item) {
			$controller = mb_strtolower(get_class($item));
			$action = 'edit';
			$title = $item->title;

			switch ($controller) {
				case 'client':
				case 'project': 
					$action = 'view';
					break;
			}
			
			$url = build_url($controller, $action, array('id'=>$item->id));
			
			?>
			<?php echo $sep?> <span class="breadcrumb-entry"><a href="<?php echo $url?>"><?php $this->view->o($title)?></a></span> 
			<?php 
			$sep = $separator;
		}
		if ($class) {
			echo '</div>';
		}
		
	}
}

?>