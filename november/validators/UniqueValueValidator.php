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
class UniqueValueValidator implements Zend_Validate_Interface 
{
    /**
     * Array of validation failure messages
     *
     * @var array
     */
    private $messages = array();
    
    private $propertyName;
    
    public function __construct($property)
    {
        $this->propertyName = $property;
    }

    /**
     * Is the model valid? 
     * @return boolean
     */
    public function isValid($model)
    {
        // first, search for another 
        $prop = $this->propertyName;
        $value = $model->$prop;
        if (empty($value)) {
            return true;
        }
        // okay, search for it using the DB service
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $existing = $dbService->getByField(array($prop => $value), get_class($model));
        if ($existing && $existing->id != $model->id) {
            $this->messages[] = get_class($model)." with $prop value '$value' already exists";
            return false;
        }
        
        return true;
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