<?php

class Helper_AjaxDispatch extends NovemberHelper
{
    /**
     * We keep an array of already dispatched 
     * controller / action / module couplings
     * to ensure we don't recursively loop
     *
     * @var array
     */
    private static $__DISPATCHED = array();
    
    /**
     * Allows the dispatching and processing of a separate 
     * controller request from inside an existing view. 
     * 
     * Should be used for read only stuff (please!)
     *
     * @param string $controller
     * @param string $action
     * @param array $params A list of parameters to bind into the new request
     * @param string $module
     * @param array $requestParams A list of parameters to pull from the current request
     */
    public function AjaxDispatch($controller, $action, $params=array(), $module=null, $requestParams=array())
    {
        $key = $controller.'-'.$action.'-'.$module;
        if (isset(self::$__DISPATCHED[$key])) {
            za()->log("Recursive dispatch detected $key ", Zend_Log::ERR);
            return;
        }
        
        self::$__DISPATCHED[$key] = true;
        
        $ctrl = Zend_Controller_Front::getInstance();
	    $oldRequest = $ctrl->getRequest();
	    
        if (count($requestParams)) {
            foreach ($requestParams as $rp) {
                // get from the current request and stick into the new
                $value = $oldRequest->getParam($rp, '');
                $params[$rp] = $value;
            }
        }

        $id = $key;

        $params['__ignorelayout'] = 1;
        
        $url = build_url($controller, $action, $params, false, $module);
        ?>
        <div id="<?php echo $id?>" class="load-target" name="<?php echo $url?>"></div>
        <script type="text/javascript">
        $().ready(function() {
        	var dispatchElem = $('#<?php echo $id?>');
        	dispatchElem.load('<?php echo $url?>');
        	});
        </script>
        <?php 
        
        return;
        
    }
}