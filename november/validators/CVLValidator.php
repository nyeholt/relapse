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


/**
 * Ensures a property of an object is unique
 *
 */
class CVLValidator implements Zend_Validate_Interface 
{
    /**
     * Array of validation failure messages
     *
     * @var array
     */
    private $messages = array();

    /**
     * The list of values that are allowed
     */
    private $values;
    
    /**
     * Get the values
     */
    public function getValues()
    {
        return $this->values;
    }
    
    public function __construct($values)
    {
        $this->values = $values;
    }

    /**
     * Is the model valid? 
     * @return boolean
     */
    public function isValid($value)
    {
        if (in_array($value, $this->values)) return true;
        
        $this->messages[] = "$value is not a valid option (".implode(",", $this->values).")";
        return false;
    }
    
    /**
	* Get validation erro messages
	 *
	 * @return array
	 */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
	 * Get validation erro messages
	 *
	 * @return array
	 */
    public function getErrors()
    {
        return $this->messages;
    }
}
?>