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
include_once dirname(__FILE__).'/CompositeView.php';
class MasterView extends CompositeView 
{
    /**
     * The child view contents
     *
     * @var unknown_type
     */
    protected $childView;
    
    /**
     * When a master view is created, it is passed its
     * script immediately, but its script path is
     * in a different location
     *
     * @param unknown_type $viewFile
     */
    public function __construct($script='', $path='views/layouts')
    {
        parent::__construct($script);
        $this->setViewFile('layouts/'.$script);
        // $this->setScriptPath(APP_DIR.'/'.$path);
    }
    
    /**
     * Set which view to wrap around
     *
     * @param unknown_type $content
     */
    public function setChildView($content)
    {
        $this->childView = $content;
    }
    
/**
     * Get the items that need to be output in the head section of this view
     * 
     * @return Map
     */
    public function getHeadItems()
    {
    	$items = $this->headItems;
    	// get the ones for the child view if set
		if ($this->childView != null) {
			$childitems = $this->childView->getHeadItems();
			$items = array_merge($items, $childitems);
		}
    	return $items;
    }
}
?>