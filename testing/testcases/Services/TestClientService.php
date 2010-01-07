<?php

class TestClientService extends UnitTestCase 
{
    public function testCreateClient()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('client');
        
        $clientService = za()->getService('ClientService');
        /* @var $clientService ClientService */
        
        $params['title'] = 'Client';
        
        $client = $clientService->saveClient($params);
        
        $project = $clientService->getClientSupportProject($client);
        $this->assertEqual('Client Support', $project->title);
        
        // Try again to force loading. 
        $project = $clientService->getClientSupportProject($client);
        $this->assertEqual('Client Support', $project->title);
    }
}
?>