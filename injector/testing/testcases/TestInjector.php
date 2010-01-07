<?php

include_once dirname(dirname(dirname(__FILE__))).'/NovemberInjector.php';

class TestInjector extends UnitTestCase
{
    public function testBasicInjector()
    {
        $injector = new NovemberInjector(dirname(dirname(__FILE__)).'/services');
        
        $injector->loadServices();
        $this->assertTrue($injector->hasService('SampleService'));
                
        $myObject = new TestObject();
        $injector->inject($myObject);
        
        $this->assertEqual(get_class($myObject->sampleService), 'SampleService');
    }
    
    public function testConfiguredInjector()
    {
        $injector = new NovemberInjector(dirname(dirname(__FILE__)).'/services');
        
        $services = array (
		    'AnotherService' => 
		    array (
		      'replace' => 'SampleService',
		      'config_property' => 'Value',
		    ),
		);

        $injector->loadServices($services);
        $this->assertTrue($injector->hasService('SampleService'));
        // We expect a false because the 'AnotherService' is actually
        // just a replacement of the SampleService
	    $this->assertFalse($injector->hasService('AnotherService'));
        
        $myObject = new TestObject();
        
        $injector->inject($myObject);
        
        $this->assertEqual(get_class($myObject->sampleService), 'AnotherService');
        $this->assertEqual($myObject->sampleService->testValue, 'Value');
    }
    
    public function testAutoSetInjector()
    {
        $injector = new NovemberInjector(dirname(dirname(__FILE__)).'/services');
        $injector->addAutoProperty('auto', 'somevalue');
        $injector->loadServices();

        $this->assertTrue($injector->hasService('SampleService'));
        // We expect a false because the 'AnotherService' is actually
        // just a replacement of the SampleService
	    
        $myObject = new TestObject();
        
        $injector->inject($myObject);
        
        $this->assertEqual(get_class($myObject->sampleService), 'SampleService');
        $this->assertEqual($myObject->auto, 'somevalue');
    }
}

class TestObject
{
    public $sampleService;
}
?>