<?php

class Helper_Pager
{
    /**
     * @param int $total The total number of things being shown
     * @param int $perPage How many things per page
     * @param string $name the name of the page
     * @param array $params additional params to use
     * @param int $currentPage
     */
	public function Pager($total, $perPage, $name='page', $params=array(), $persistParams=false, $currentPage = 0, $prev = '&laquo;', $next='&raquo;', $numberOfLinks=10)
	{
	    if ($total == 0 || $perPage == 0) {
	        return;
	    }
	    if ($total <= $perPage) {
	        return;
	    }
	    
	    // If not set, check the request, otherwise just
	    // assume first page
	    if (!$currentPage) {
	        $currentPage = ifset($_GET, $name, 1);
	    }
	    
	    
	    $totalPages = ceil($total / $perPage);
	    
	    
	    // Figure out where to start printing from
	    $eitherSide = floor($numberOfLinks / 2);
	    
	    $start = 1;
	    $end = $numberOfLinks;
	    
	    
	    if ($currentPage > $eitherSide) {
	        $start = $currentPage - $eitherSide;
	        $end = $currentPage + $eitherSide;
	    }
	    
	    // Account for the extra item we get if we're 
	    // an even numberoflinks value
	        $end--;
	    
	    if ($end > $totalPages) $end = $totalPages;
	    
	    $args = '';
	    $ctrl = Zend_Controller_Front::getInstance();
	    $oldRequest = $ctrl->getRequest()->getParams();
	    
	    if ($persistParams) {
	    	foreach ($oldRequest as $param => $value) {
	    		if ($param != 'action' && $param != 'controller' && $param != 'module' && $param != $name && !isset($params[$param])) {
	    			$params[$param] = $value;
	    		}
	    	}
	    }
	    
	    $args = '&amp;' . encode_params($params, '&amp;', '=');

	    echo '<div class="pager-links">';
	    if ($currentPage > 1) {
	       echo '<a class="pager-prev-link pager-link" href="'.current_url().'?'.$name.'='.($currentPage-1).$args.'">'.$prev.'</a>';
	    }
	    for ($i = $start; $i <= $end; $i++) {
	        if ($i == $currentPage) {
	            echo '<span class="pager-current-link pager-link">'.$i.'</span>';
	        } else {
	           echo '<a class="pager-link" href="'.current_url().'?'.$name.'='.$i.$args.'">'.$i.'</a>';
	        }
	    }
	    
	    if ($currentPage < $totalPages) {
	       echo '<a class="pager-next-link pager-link" href="'.current_url().'?'.$name.'='.($currentPage+1).$args.'">'.$next.'</a>';
	    }
	    echo '</div>';
	}
}

?>