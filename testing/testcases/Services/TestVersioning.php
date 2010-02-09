<?php

class TestVersioning extends UnitTestCase
{

    /**
     * Test getting the list of users.
     *
     */
    public function testCreateVersion()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('objectversion');
		$dbService->delete('client');

        $versioningService = za()->getService('VersioningService');
        /* @var $versioningService VersioningService */

        $clientService = za()->getService('ClientService');
        /* @var $clientService ClientService */
        $params['title'] = 'Client';
        $client = $clientService->saveClient($params);

		// version the client
		$version = $versioningService->createVersion($client);

		$this->assertEqual($client->id, $version->objectid);
		$this->assertEqual(get_class($client), $version->objecttype);

		$this->assertEqual($client->id, $version->item->id);

		// create another version, make sure that it sets the correct 'from'
		// date
		$currentCreate = $version->created;
		$newVersion = $versioningService->createVersion($client);
		$this->assertEqual($currentCreate, $newVersion->validfrom);

		// try fetching
		$versions = $versioningService->getVersionsFor($client);
		
		$this->assertEqual(2, count($versions));

		// try from / to

		sleep(3);

		$version = $versioningService->createVersion($client);

		$versions = $versioningService->getVersionsFor($client, date('Y-m-d H:i:s', time() - 2));
		$this->assertEqual(1, count($versions));
	}
	
}
?>