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
 * An exception that can be thrown when a model object
 * is invalid
 *
 */
class InvalidModelException extends Exception 
{
    /**
     * An array of error messages
     *
     * @var unknown_type
     */
    private $messages = array();
    
    /**
     * Create the exception using a list of invalid 
     * properties
     *
     * @param array $messages
     */
    public function __construct($messages = array())
    {
        parent::__construct("Invalid model");
        $this->messages = $messages;
    }
    
    /**
     * Get the errors
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
?>