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
        $dbService->delete('clientversion');
		$dbService->delete('client');

        $versioningService = za()->getService('VersioningService');
        /* @var $versioningService VersioningService */

        $clientService = za()->getService('ClientService');
        /* @var $clientService ClientService */
        $params['title'] = 'Client';
        $client = $clientService->saveClient($params);

		// version the client
		$version = $versioningService->createVersion($client);

		$this->assertEqual($client->id, $version->recordid);
		$this->assertEqual(get_class($version), 'ClientVersion');

		// create another version, make sure that it sets the correct 'from'
		$currentCreate = date('Y-m-d H:i:s', strtotime($version->created) + 1);
		$newVersion = $versioningService->createVersion($client);
		$this->assertEqual($currentCreate, $newVersion->validfrom);

		// try fetching
		$versions = $versioningService->getVersionsFor($client);
		
		$this->assertEqual(3, count($versions));

		// try from / to

		sleep(3);

		$version = $versioningService->createVersion($client);

		$versions = $versioningService->getVersionsFor($client, date('Y-m-d H:i:s', time() - 2));
		$this->assertEqual(1, count($versions));

		$allVersions = $versioningService->getVersionsFor('Client');

		// we expect 4 because clients are versioned on creation
		$this->assertEqual(4, count($allVersions));
	}

	function testObjectSnapshot()
	{
		$dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('clientversion');
		$dbService->delete('client');

        $versioningService = za()->getService('VersioningService');
        /* @var $versioningService VersioningService */

        $clientService = za()->getService('ClientService');
        /* @var $clientService ClientService */
        
		$params['title'] = 'Client1';
        $client1 = $clientService->saveClient($params);

		$params['title'] = 'Client2';
        $client2 = $clientService->saveClient($params);

		$params['title'] = 'Client3';
        $client3 = $clientService->saveClient($params);

		sleep(2);
		$beforeChanges = date('Y-m-d H:i:s');
		sleep(2);
		$client2->title = 'Client 2';
		$versioningService->createVersion($client2);
		$clientService->saveClient($client2);
		sleep(2);
		$client3->title = 'Client 3';
		$versioningService->createVersion($client3);
		$clientService->saveClient($client3);
		sleep(2);
		$spacesDate = date('Y-m-d H:i:s');
		sleep(2);
		$client2->title = 'Client Two';
		$versioningService->createVersion($client2);
		$clientService->saveClient($client2);
		sleep(2);

		$now = date('Y-m-d H:i:s');

		$unchanged = $versioningService->getObjectSnapshot('Client', $beforeChanges);
		$spacesVersions = $versioningService->getObjectSnapshot('Client', $spacesDate);
		$nowVersions = $versioningService->getObjectSnapshot('Client', $now);

		$this->assertEqual(3, count($unchanged));
		$this->assertEqual(3, count($spacesVersions));
		$this->assertEqual(3, count($nowVersions));
	}
}
?>