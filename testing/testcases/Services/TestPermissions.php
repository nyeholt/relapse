<?php
/* All code covered by the BSD license located at http://silverstripe.org/bsd-license/ */

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class TestPermissions extends UnitTestCase {

	protected function setupDefaultObjects() {
		$dbService = za()->getService('DbService');
		$types = za()->getService('TypeManager');

		$types->includeType('UserRole');
		$types->includeType('Client');
		$types->includeType('Issue');

        $dbService->delete('userrole');

		$dbService->delete('client');
		$dbService->delete('issue');

		$client = new Client();
		$client->title = "A Client";
		za()->inject($client);
		$dbService->saveObject($client);

		$issue = new Issue();
		za()->inject($issue);
		$issue->title = "This is a complaint";
		$issue->clientid = $client->id;
		$dbService->saveObject($issue);
		

		$client = new Client();
		$client->title = "B Client";
		za()->inject($client);
		$dbService->saveObject($client);

		$issue = new Issue();
		za()->inject($issue);
		$issue->title = "This is b complaint";
		$issue->clientid = $client->id;
		$dbService->saveObject($issue);
	}

	protected $testUsers = null;

	protected function createUsers() {
		if (!$this->testUsers) {
			$this->testUsers = new stdClass();

			$dbService = za()->getService('DbService');
			$users = za()->getService('UserService');
			$dbService->delete('crmuser');

			$params = array(
				'email' => 'user1@here.com',
				'username' => 'user1@here.com',
				'password' => 'password',
			);

			$this->testUsers->user1 = $users->createUser($params);

			$params = array(
				'email' => 'user2@here.com',
				'username' => 'user2@here.com',
				'password' => 'password',
			);

			$this->testUsers->user1 = $users->createUser($params);

			$params = array(
				'email' => 'user3@here.com',
				'username' => 'user3@here.com',
				'password' => 'password',
			);

			$this->testUsers->user1 = $users->createUser($params);

		}
	}

    public function testPermissionApplication() {
        $dbService = za()->getService('DbService');
		$this->setupDefaultObjects();

        $plainitems = $dbService->getObjects('Issue');
        $authService = za()->getService('AuthService');

        foreach ($plainitems as $item) {
            $authService->grantAccess($item, za()->getUser(), UserRole::getRole(UserRole::ROLE_ADMIN));
        }

        // $where=array(), $order='id asc', $page=null, $number=null, $auth='')
        $items = $dbService->getObjects('Issue', array(), null, null, null, UserRole::PERM_READ);
        $this->assertEqual(count($items), count($plainitems));

        // $where=array(), $order='id asc', $page=null, $number=null, $auth='')
        $items = $dbService->getObjects('Issue', array(), null, null, null, UserRole::PERM_WRITE);
        $this->assertEqual(count($items), count($plainitems));

        $items = $dbService->getObjects('Issue', array(), null, null, null, UserRole::PERM_ADMIN);
        $this->assertEqual(count($items), count($plainitems));

        $item = current($items);
        $authService->removeAccess($item, za()->getUser());

        $items = $dbService->getObjects('Issue', array(), null, null, null, UserRole::PERM_ADMIN);
        $this->assertEqual(count($items), count($plainitems)-1);
    }
}