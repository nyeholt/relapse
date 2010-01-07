<?php
class Helper_SortHeader extends NovemberHelper 
{
    public function SortHeader($title, $sortField, $url = '', $sortKey = 'sort', $dirKey = 'dir', $ajax=false, $persistParams=true)
	{
		if ($url == '') {
			$url = current_url();
		}
	    
	    $ctrl = Zend_Controller_Front::getInstance();
	    $openTag = '';
	    $oldRequest = $ctrl->getRequest();
	    
	    $dir = $oldRequest->getParam($dirKey, 'up');
	    
	    $params = array();

		if ($persistParams) {
			$oldRequest = $oldRequest->getParams();
	    	foreach ($oldRequest as $param => $value) {
	    		if ($param != 'action' && $param != 'controller' && $param != 'module' && $param != $dirKey && $param != $sortKey) {
	    			$params[$param] = $value;
	    		}
	    	}
	    }

	    $args = '';
		foreach ($params as $key => $value) {
			if ($key == 'id') continue;
	        if (is_string($value) && strpos($value, '#') === 0) {
	            $args .= $value;
	        } else {
	            if (is_array($value)) {
	                foreach ($value as $val) {
	                    $args .= "&amp;".urlencode($key.'[]').'='.urlencode($val);
	                }
	            } else {
    	            $args .= "&amp;".urlencode($key).'='.urlencode($value);
	            }
	        }
	    }
	    
	        if ($dir == 'up') {
	            $dir = 'down';
	        } else {
	            $dir = 'up';
	        }

	    $url .= '?'.$sortKey.'='.$sortField.'&amp;'.$dirKey.'='.$dir.$args;
	    
	    if ($ctrl->getRequest()->getParam('__ajax') || $ajax) {
	        $tag = '<a href="#" class="ajax-sort-header" target="'.$url.'">';
	    } else {
	        $tag = '<a href="'.$url.'" class="sort-header">';
	    }

	    echo $tag.$title.'</a>';
	}
}
?>