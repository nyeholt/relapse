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

class TestModel extends UnitTestCase 
{
    public function testBindableModel()
    {
        $properties = array('variable'=>'value', 'othervariable'=>'othervalue');
        
        include_once APP_DIR.'/model/SampleModel.php';
        
        $sample = new SampleModel();
        $sample->bind($properties);
        
        $this->assertEqual($sample->variable, 'value');    
        $this->assertEqual($sample->othervariable, 'othervalue');
    }
    
    public function testValidateModel()
    {
        $properties = array('variable'=>'value', 'othervariable'=>'othervalue@email.com', 'integer' => 3);
        
        include_once APP_DIR.'/model/SampleModel.php';
        include_once APP_DIR.'/model/EmptyModel.php';
        
        $modelValidator = new ModelValidator();
        
        $empty = new EmptyModel();
        $this->assertTrue($modelValidator->isValid($empty));
        
        $sample = new SampleModel();
        // No constraints, so should be valid
        $this->assertTrue($modelValidator->isValid($sample));
        
        // Add some constraints and make sure it fails validation
        $constraints = array(
        'variable' => 'Zend_Validate_Alnum',
        'othervariable' => 'Zend_Validate_EmailAddress',
        'integer' => new Zend_Validate_Between(4, 8),
        );
        
        $sample->constraints = $constraints;
        
        // No longer a valid test due to the introduction of the
        // required field validators.
        // $this->assertFalse($modelValidator->isValid($sample));
        
        $sample->bind($properties);
        $this->assertEqual($sample->variable, 'value');    
        $this->assertEqual($sample->othervariable, 'othervalue@email.com');
        
        // Should fail because 3 falls outside the sample's constraints. 
        $this->assertFalse($modelValidator->isValid($sample));
        
        $sample->integer = 5;
        
        $this->assertTrue($modelValidator->isValid($sample));
    }
    
    function testArrayObject()
    {
        $obj = new ArrayObject();
        
        $obj[2] = "something";
        
        $this->assertTrue($obj->offsetExists(2));
        $this->assertFalse($obj->offsetExists('2'));
        
        $this->assertFalse($obj->offsetExists(1));
    }
    
    function testRequiredFields()
    {
        $properties = array('variable'=>'avalue');
        
        include_once APP_DIR.'/model/SampleModel.php';
        include_once APP_DIR.'/model/EmptyModel.php';
        
        $modelValidator = new ModelValidator();
        
        $sample = new SampleModel();
        // No constraints, so should be valid
        $this->assertTrue($modelValidator->isValid($sample));
        
        $requiredFields = array(
        'variable',
        );
        
        $sample->requiredFields = $requiredFields;
        
        $this->assertFalse($modelValidator->isValid($sample));
        
        $sample->bind($properties);
        $this->assertEqual($sample->variable, 'avalue');    
        
        // Should pass 
        $this->assertTrue($modelValidator->isValid($sample));
        
        $sample->variable = '';
        
        // Should now fail
        $this->assertFalse($modelValidator->isValid($sample));
    }
}
?>