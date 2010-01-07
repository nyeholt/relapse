<?php

include_once APP_DIR.'/model/Contact.php';

class TestContact extends UnitTestCase 
{
    function testValidation()
    {
        $contact = new Contact();
        $validator = new ModelValidator();
        $this->assertFalse($validator->isValid($contact));
        $msgs = $validator->getMessages();
        
        $this->assertEqual(1, count($msgs));
        
        // Set an email address that's invalid
        $contact->firstname = 'Firstname';
        $contact->email = 'nothing';
        
        $this->assertFalse($validator->isValid($contact));
        
        $contact->email = 'nothing@nothing.com';
        $this->assertTrue($validator->isValid($contact));
    }
}
?>