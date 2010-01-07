<?php
/**
 * This file belongs to the November framework, an extension of the
 * Zend Framework, written by Marcus Nyeholt <marcus@mikenovember.com>
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to marcus@mikenovember.com so I can send you a copy immediately.
 *
 * @package   November
 * @copyright  Copyright (c) 2006-2007 Marcus Nyeholt (http://mikenovember.com)
 * @version    $Id$
 * @license    New BSD License
 */

if(!in_array('view', stream_get_wrappers())) {
    stream_wrapper_register('view', 'ViewStream');
}


class CompositeView extends Zend_View_Abstract
{
    public static $FLASH_KEY = '__FLASH_VALUE';
    
    public $did;
    
    /**
     * The parent view of the current view, if any
     *
     * @var unknown_type
     */
    protected $parent = null;

    /**
     * The "master" page allows you to apply a global 
     * template around an item. To register a view with the
     * master template, simply call $this->setMaster('MasterViewName')
     * which will then execute that master view. A master view can then
     * use "echo $this->childView"
     *
     * @var MasterView
     */
    protected $master = null;
    
    /**
     * To prevent being rendered twice, we
     * record whether we've already passed rendering
     * to our parent; if so, we don't do it again 
     * otherwise we end up with an infinite loop
     *
     * @var boolean
     */
    protected $renderedMaster = false;

    /**
     * Which file is the template?
     *
     * @var unknown_type
     */
    protected $viewFile;
    
    public function getViewFile()
    {
        return $this->viewFile;
    }
    
    protected function setViewFile($file)
    {
        $this->viewFile = $file;
    }
    
    /**
     * Are there any errors to display for this view?
     *
     * @var unknown_type
     */
    protected $errors = array();
    
    /**
     * A oneshot message flash
     *
     * @var string
     */
    protected $flash = "";
    
    /**
     * Indicates whether to persist the value in
     * flash through to the next page. 
     *
     * @var boolean
     */
    protected $persistFlash = false;
    
    /**
     * A list of all items that should be registered for outputting in the 'head' section
     * @var map
     */
	protected $headItems = array();
    
    /**
     * When a master view is created, it is passed its
     * script immediately. 
     *
     * @param unknown_type $viewFile
     */
    public function __construct($script='')
    {
        $this->setViewFile($script);
        if (is_array($script)) {
            parent::__construct($script);
        } else {
            parent::__construct();
        }
        
        $this->addHelperPath(APP_DIR.'/helpers', 'Helper');
    }

    /**
     * Get the parent of this view
     *
     * @return CompositeView
     */
    protected function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the parent of this view
     *
     * @param CompositeView $p
     */
    public function setParent(CompositeView $p)
    {
        $this->parent = $p;
    }

    /**
     * Use the view filtering. 
     *
     */
    protected function _run()
    {
        include func_get_arg(0); //'view://'.func_get_arg(0);
    }

    public function __set($key, $val)
    {
        // If the item being set is a composite view, we'll set
        // its parent object
        if ($val instanceof CompositeView) {
            $val->setParent($this);
        }

        parent::__set($key, $val);
    }

    public function toString()
    {
        return (string) $this->render('null');
    }
    
    /**
     * Set up the script paths
     */
    private function setScriptPaths()
    {
    	
        $this->addScriptPath(APP_DIR.'/views');
    
        // get the configured theme, if any. Will override any views
		// from the base location if it exists in the non-default theme
		$theme = $theme = za()->getUser()->getTheme();
		if ($theme != '' && is_dir(APP_DIR.'/themes/'.$theme)) {
			// set this theme as the one to be used
			$this->addScriptPath(APP_DIR.'/themes/'.$theme);
		}
    }
    
    /**
     * When rendering, check to see if there's a master view
     * to also render.
     *
     * @return string the render result
     */
    public function render($view)
    {
    	$this->setScriptPaths();
		
        if ($view == null) {
            echo "NO VIEW SUPPLIED<br/>";
            debug_print_backtrace();
            exit();
        }

        $viewToRender = $view;
        
        if ($this->viewFile != '') {
            $viewToRender = $this->viewFile;
        } else {
            $this->setViewFile($view);
        }

        // first off, render this view.  
        $result = parent::render($viewToRender);

        // If there's a parent view to be rendered, render it, after setting 
		// this view's content as a variable in that parent view. 
        if ($this->master != null) {
            $this->master->setChildView($this);
            $this->master->childViewContent = $result;
            $result = $this->master->render('null');
        }

        return $result;
    }

    /**
     * Adds an error that this view can expose
     *
     * @param string $id
     * @param string $string
     */
    public function addError($id, $string)
    {
        $this->errors[$id] = $string;
    }

    /**
     * Add an array of errors.
     *
     * @param array $errors
     */
    public function addErrors($errors)
    {
        $this->errors += $errors;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Add a message to flash to the user
     * 
     * Records whether this is a new flash in the
     * request or not. 
     * 
     * @param string
     * @param persistFlash
     */
    public function flash($string = null, $persistFlash = true)
    {
        if (!is_null($string)) {
            $this->persistFlash = $persistFlash;
            if ($persistFlash) {                
                $_SESSION[self::$FLASH_KEY] = $string;
            }
            $this->flash = $string; 
        }
        else return $this->flash;
    }
    
    /**
     * Tells whether this is a new flash or not
     *
     * @return boolean
     */
    public function isPersistentFlash()
    {
        return $this->persistFlash;
    }
    
    /**
     * Set the master of the current page. 
     *
     * @param unknown_type $viewName
     */
    public function setMaster($viewName)
    {
        $view = Zend_Registry::get($viewName);
        if ($view == null) {
            throw new Exception("View $viewName does not exist in the registry");
        }
        $this->master = $view;
    }
    
    /**
     * If for some reason we don't want to 
     * display the wrapper master template (ie JS response)
     * set this flag.
     * 
     * @return CompositeView
     *
     */
    public function clearMaster()
    {
        $this->master = null;
        return $this;
    }
    
    /**
     * Add something to be output in the header of the page
     * 
     * @param String $key 
     * @param String $item the item to be output
     */
    public function addHeadItem($key, $item)
    {
    	if (!isset($this->headItems[$key])) {
    		$this->headItems[$key] = $item;
    	}
    }
    
    /**
     * Get the items that need to be output in the head section of this view
     * 
     * @return Map
     */
    public function getHeadItems()
    {
    	return $this->headItems;
    }
}


class ViewStream
{
    protected $data;
    protected $length;
    protected $status;
    protected $position;

    public function stream_open($file, $mode, $options, &$opened_path){

        $this->position = 0;
        $file = str_replace('view://', '', $file);
        $this->data = file_get_contents($file);

        if($this->data === false){
            $this->status = stat($file);
            return false;
        }else{
            $this->length = strlen($this->data);
        }

        $parser = new ViewParser();

        /*
        if(!ini_get('short_open_tag')){
            $search[] = '<? ';
            $search[] = '<?=';
            $replace[] = '<?php ';
            $replace[] = '<?php echo ';
            $this->data = str_replace($search, $replace, $this->data);
        }
        
        $this->data = str_replace('@$', '$this->', $this->data);
        $this->data = str_replace('parent->', 'getParent()->', $this->data);
        */

        $this->data = $parser->parse($this->data);

        $this->status = array('mode' => 0100777, 'size' => strlen($this->data));

        return true;
    }

    public function stream_stat() {
        return $this->status;
    }

    public function stream_eof() {
        return $this->position >= $this->length;
    }

    public function stream_tell(){
        return $this->position;
    }

    public function stream_read($count) {
        $data = substr($this->data, $this->position, (int)$count);
        $this->position += strlen($data);
        return $data;
    }

    public function stream_seek($offset, $type) {
        switch($type){
            case SEEK_SET:
                if($offset >= 0 && $this->length > $offset){
                    $this->position = $offset;
                    return true;
                }
                break;
            case SEEK_CUR:
                if($offset >= 0){
                    $this->position += $offset;
                    return true;
                }
                break;
            case SEEK_END:
                if($this->length + $offset >= 0){
                    $this->position = $this->length + $offset;
                    return true;
                }
                break;
        }
        return false;
    }

}

class NovemberHelper
{
    protected $view;
    
    public function setView($view)
    {
        $this->view = $view;
    }
}

/**
 * Simple worker class to parse through the passed in stream and
 * replace items of interest
 *
 */
class ViewParser
{
    private $commandHandlers;

    public function __construct()
    {
        $this->commandHandlers = array();
        $this->commandHandlers['='] = 'raw';
        $this->commandHandlers['@'] = 'escape';
    }
    
    public function parse($template)
    {
        $length = strlen($template);
        $i = 0;
        $output = '';
        $previous = '';
        
        while ($i < $length) {
            $char = $template{$i};
            
            if ($char == '%' && $previous == '<') {

                $command = '';
                $inner_prev = '';
                $inner_current = '';

                $i++;
                
                // process through until the closing % >
                while (!($inner_current == '>' && $inner_prev == '%') && $i < $length) {
                    $inner_current = $template{$i};
                    if ($inner_current == '>' && $inner_prev == '%') {
                        continue;
                    } else if ($inner_prev == '%') {
                        // If it's not the closing tag, we'll output it both
                        $command .= $inner_prev.$inner_current;
                        $inner_prev = $inner_current;
                    } else if ($inner_current == '%') {
                        // just consume it for now...
                        $inner_prev = $inner_current;
                    } else {
                        $inner_prev = $inner_current;
                        $command .= $inner_prev;
                    }

                    // $inner_prev = $inner_current;
                    $i++;
                }
                
                if ($i > $length) {
                    throw new Exception("Parse error: Missing closing tag for <@ ");
                }
                
                
                    
                // Get rid of the
                $replacement = $this->getCommandReplacement($command);
                
                // Insert the replacement.
                $output .= '<?php '.$replacement.' ?>';
                
            } else if ($previous == '<') {
                
                $output .= $previous.$char;
                
            } else if ($char == '<') {
                // If we're examining the < character, we don't want to
                // output it until we see the next character and whether it's
                // not an %
            }
            
            else {
                $output .= $char;
            }

            $i++;
            $previous = $char;
        }
        
        $template = $output;
        // error_log($output."\n\n\n"); //exit();
        
        return $template;
    }
    
    
    
    private function getCommandReplacement($command)
    {
        // first one: short_open <%= 
        $singleChar = $command{0};
        $newCommand = '';
        $length = strlen($command);
        $i=1;
        while ($i < $length) {
            $newCommand .= $command{$i};
            $i++;
        }
        $replacement = '';
        if (isset($this->commandHandlers[$singleChar])) {
            $replacement = call_user_func_array(array($this, $this->commandHandlers[$singleChar]), array($newCommand));
        }
        return $replacement;
        
    }
    
    private function escape($command)
    {
        $cmd = 'echo htmlentities('.$command.', ENT_COMPAT, \'UTF-8\')';
        return $cmd;
    }
    
    
    private function raw($command)
    {
        $cmd = 'echo '.$command;
        return $cmd;
    }
    
}

?>