<?php
class TestSearchService extends UnitTestCase  
{
    public function testAddDocument()
    {
        $configuration = za()->getConfig('services');
        if (!$configuration) {
            echo "No services config found\n";
            return;
        }
        $searchConfig = ifset($configuration, 'SearchService');
        if (!$searchConfig) {
            echo "SearchService config not found\n";
            return;
        }
        
        $path = ifset($searchConfig, 'index');
        if (!$path) {
            echo "No search path set\n";
            return;
        }
        
        // Delete the search path
        rmdirr($path);
        $searchService = za()->getService('SearchService');
        
        $example = new Task();
        $example->id = 1;
        $example->title = 'Task for testing';
        $example->description = "Task description for testing";

        try {
            $searchService->index($example);
        } catch (Exception $e) {
            print_r($e);
        }
        
        $results = $searchService->search("testing");
        
        $this->assertEqual(count($results), 1);
        
    }
    
    public function testDelete()
    {
        $searchService = za()->getService('SearchService');
        
        $example = new Task();
        $example->id = 1;
        $example->title = 'Task for testing';
        $example->description = "Task description for testing";
        
        $searchService->delete($example);
        $results = $searchService->search("testing");
        
        $this->assertEqual(count($results), 0);
    }
}
?>