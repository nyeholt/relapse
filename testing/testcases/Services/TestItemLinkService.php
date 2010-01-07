<?php

class TestItemLinkService extends UnitTestCase 
{
    public function testCreateParentChildLink()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('client');
        $dbService->delete('itemlink');
        
        $clientService = za()->getService('ClientService');
        /* @var $clientService ClientService */
        
        $params['title'] = 'Client';
        
        $client = $clientService->saveClient($params);
        
        $project = $clientService->getClientSupportProject($client);
        $this->assertEqual('Client Support', $project->title);
        
        
        // Now use the item link service to create a link
        $itemLinkService = za()->getService('ItemLinkService');
        /* @var $itemLinkService ItemLinkService */
        
        $link = $itemLinkService->parentChildLink($client, $project);
        
        $this->assertEqual($link->fromid, $client->id);
        $this->assertEqual($link->toid, $project->id);
        
        try {
            $itemLinkService->parentChildLink($client, $project);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
}
?>