<?php

class Helper_AjaxPager
{
    /**
     * @param int $total The total number of things being shown
     * @param int $perPage How many things per page
     * @param string $name the name of the page
     * @param array $params additional params to use
     * @param int $currentPage
     */
	public function AjaxPager($target, $total, $perPage, $name='page', $targetUrl='', $params=array(), $persistParams=false, $currentPage = 0, $prev = '&laquo;', $next='&raquo;', $numberOfLinks=10)
	{
	    
	    if ($total == 0 || $perPage == 0) {
	        return;
	    }
	    if ($total <= $perPage) {
	        return;
	    }
	    
	    if ($targetUrl == '') {
	        $targetUrl = current_url();
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
	    		if ($param == 'id') continue;
	    		if ($param != 'action' && $param != 'controller' && $param != 'module' && $param != $name && !isset($params[$param])) {
	    			$params[$param] = $value;
	    		}
	    	}
	    }
	    
	    $args = '&amp;' . encode_params($params, '&amp;', '=');

	    /*foreach ($params as $key => $value) {
	        if (strpos($value, '#') === 0) {
	            $args .= $value;
	        } else {
	            $args .= "&amp;".urlencode($key).'='.urlencode($value);
	        }
	    }*/


	    $html = '';
	    $html .= '<div class="pager-links">';
	    if ($currentPage > 1) {
	       $html .= '<a href="javascript:void(0);" class="pager-prev-link pager-link '.$target.'-pager-link" target="'.$targetUrl.'?'.$name.'='.($currentPage-1).$args.'">'.$prev.'</a>';
	    }
	    for ($i = $start; $i <= $end; $i++) {
	        if ($i == $currentPage) {
	            $html .= '<span class="pager-current-link pager-link">'.$i.'</span>';
	        } else {
	           $html .= '<a href="javascript:void(0);" class="pager-link '.$target.'-pager-link" target="'.$targetUrl.'?'.$name.'='.$i.$args.'">'.$i.'</a>';
	        }
	    }
	    
	    if ($currentPage < $totalPages) {
	       $html .= '<a href="javascript:void(0);" class="pager-next-link pager-link '.$target.'-pager-link" target="'.$targetUrl.'?'.$name.'='.($currentPage+1).$args.'">'.$next.'</a>';
	    }
	    $html .= '</div>';
	    
	    echo $html;
	    // so now make an ajax request and return the content into the $targetContainer
        ?>
        <script type="text/javascript">
        	$().ready(function() {
        		bindPagerLinks();
        	});

			var pagerLinksLoading = false;
        	function bindPagerLinks()
        	{
        		$('.<?php echo $target;?>-pager-link').click(function() {
        			var target = $(this).attr('target')+'&__ajax=1';
        			
        			if (!pagerLinksLoading) {
	        			pagerLinksLoading = true;
    	    			$.get(target, processAjaxPagerData);
    	    		} 
        			return false;
        		});
        	}

        	// Once the incoming data is in, we need to re-bind all the
        	// paging links so they work properly in things like ie
        	function processAjaxPagerData(data) {
        		pagerLinksLoading = false;
        		$('#<?php echo $target;?>').html(data);
        		bindPagerLinks();				        		
        	}
        </script>
        
        <?php
	}
}

?>