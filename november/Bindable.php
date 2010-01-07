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

abstract class Bindable
{
    /**
     * Bind all the variables into this object, 
     * but ONLY if it's already declared
     *
     * @param array $variables
     */
    public function bind($variables)
    {
        $reflect = new ReflectionObject($this);
        $properties = $reflect->getProperties();

        foreach ($variables as $key => $var) {
            // Protected from alteration!
            if ($key == 'constraints' || $key == 'requiredFields' || $key == 'searchableFields') continue;
            if ($reflect->hasProperty($key) != null) {
                $reflect->getProperty($key)->setValue($this, $var);
            }
        }
    }
    
    /**
     * Classes are occasionally serialized, make sure that no services are 
     * included in that
     *
     * @return array
     */
	public function __sleep() {
		$keys = array();
        foreach ($this as $key => $value) {
        	if (!endswith($key, 'Service') && ($key != 'log')) {
            	$keys[] = $key;
        	}
        } 
        return $keys;
    }
}