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

class ModelValidator implements Zend_Validate_Interface 
{
    /**
     * Array of validation failure messages
     *
     * @var array
     */
    private $messages = array();

    /**
     * Is the model valid? 
     * @return boolean
     */
    public function isValid($model)
    {
        $isValid = true;
        $this->messages = array();
        // Process the constraints
        if (isset($model->constraints)) {
            foreach ($model->constraints as $property => $constraints) {
                $toValidate = $property == '__this' ? $model : $model->$property;
                // If the value is empty, we'll skip trying to validate it
                // and leave it for the required field validation
                if (empty($toValidate)) {
                    continue;
                }
                if (!is_array($constraints)) { 
                    $constraints = array($constraints);
                }
                
                foreach ($constraints as $constraint) {
                    // If the constraint is a string, we create an instance
                    // of the class it describes. If it's an object, we
                    // use that instead
                    if (is_string($constraint)) {
                        $constraint = new $constraint;
                    }
                    
                    $toValidate = $property == '__this' ? $model : $model->$property;
                    if (!$constraint->isValid($toValidate)) {
                        $this->messages[$property] = $constraint->getErrors();
                        $isValid = false;
                    }
                }
            }
        }
        
        if (isset($model->requiredFields)) {
            foreach ($model->requiredFields as $property) {
                $value = $model->$property;
                if (empty($value)) {
                    $this->messages[$property] = "$property is a required field";
                    $isValid = false;
                }
            }
        }
        
        return $isValid;
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