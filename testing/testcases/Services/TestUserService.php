<?php 

class TestUserService extends UnitTestCase  
{

    /**
     * Test getting the list of users.
     *
     */
    public function testGetUsers()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('crmuser');
        
        $userService = za()->getService('UserService');
        /* @var $userService UserService */
        
        $params['username'] = 'admin';
        $params['email'] = 'nyeholt@gmail.com';
        $params['password'] = 'admin';
        $newUser = $userService->createUser($params);
        
        $params['username'] = 'root';
        $params['email'] = 'n.yeholt@gmail.com';
        $params['password'] = 'root';
        $another = $userService->createUser($params);
        
        $user = $userService->getUserByField('username', 'admin');
        
        $this->assertEqual($newUser->id, $user->id);
        
        $users = $userService->getUserList();
        
        $this->assertEqual(2, count($users));
    }

	public function testAuthenticateUser()
	{
		$dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('crmuser');

        $userService = za()->getService('UserService');
        /* @var $userService UserService */

        $params['username'] = 'admin';
        $params['email'] = 'nyeholt@gmail.com';
        $params['password'] = 'admin';
        $newUser = $userService->createUser($params);

		
	}


    public function testGetAuthorities()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('usergroup');
        $dbService->delete('groupmember');
        
        $groupService = za()->getService('GroupService');
        $userService = za()->getService('UserService');
        /* @var $userService UserService */
        
        $params = array();
        $params['title'] = "GroupA";
        
        $groupA = $groupService->createGroup($params);
        
        $params = array();
        $params['title'] = "GroupB";
        
        $groupB = $groupService->createGroup($params);
        
        $params = array();
        $params['title'] = "GroupC";
        
        $groupC = $groupService->createGroup($params);
        
        $groups = $groupService->getGroups();
        
        $this->assertEqual(3, count($groups));
        
        // okay now add in some try adding users to groups etc
        $groupService->addToGroup($groupA, $groupB);
        
        /*
         * So group hierarchy is 
         * GroupA
         * 		- GroupB
         * 			- Group C
         */
        
        $this->assertTrue($groupB->parentpath == '-'.$groupA->id.'-', "Group parent is ".$groupB->parentpath.", should be $groupA->id");
        
        try {
            $groupService->addToGroup($groupB, $groupA);
            $this->assertTrue(false, "Missing exception");
        } catch (RecursiveGroupException $rge) {
            $this->assertTrue(true);
        }
        
        $groupService->addToGroup($groupB, $groupC);
        
        $this->assertTrue($groupC->parentpath == '-'.$groupA->id.'-'.$groupB->id.'-', "Group parent is ".$groupB->parentpath.", which is incorrect");
        
        $user1 = $userService->getUserByField('username', 'admin');
        $user2 = $userService->getUserByField('username', 'root');
        
        // okay, add the user. 
        $groupService->addToGroup($groupB, $user1);
        $groupService->addToGroup($groupC, $user2);
        
        $usersInGroup = $groupService->getUsersInGroup($groupA);
        $this->assertEqual(2, count($usersInGroup));
        
        $usersInGroup = $groupService->getUsersInGroup($groupB);
        $this->assertEqual(2, count($usersInGroup));
        
        $usersInGroup = $groupService->getUsersInGroup($groupC);
        $this->assertEqual(1, count($usersInGroup));
        
        try {
            $groupService->deleteGroup($groupB);
            $this->assertTrue(false, 'Expected exception not thrown');
        } catch (NonEmptyGroupException $neg) {
            $this->assertTrue(true);
        }
        
        // Now, make sure that user2 is in groupA, B and C
        // and that user1 is in group A and B
        $usersGroup = $groupService->getGroupsForUser($user2, false);
        $this->assertEqual(3, count($usersGroup));
        
        $groupService->deleteGroup($groupC);

        $groups = $groupService->getGroups();
        $this->assertEqual(2, count($groups));
        
        $usersInGroup = $groupService->getUsersInGroup($groupA);
        $this->assertEqual(1, count($usersInGroup));
        
        $directGroupUsers = $groupService->getUsersInGroup($groupB, true);
        $this->assertEqual(1, count($directGroupUsers));
        
        $groupService->addToGroup($groupB, $user2);
        
        $directGroupUsers = $groupService->getUsersInGroup($groupB, true);
        $this->assertEqual(2, count($directGroupUsers));
        
        $groupService->emptyGroup($groupB);
        $directGroupUsers = $groupService->getUsersInGroup($groupB, true);
        $this->assertEqual(0, count($directGroupUsers));
    }

    
    public function testLeaveCalculation()
    {
        $userService = za()->getService('UserService');
        /* @var $userService UserService */
        
        $users = $userService->getUserList();
        
        $user = $users[0];
        
        // set their start date
        $user->startdate = '2006-07-10 00:00:00';
        
        // $this->assertEqual(floor($userService->calculateLeave($user)), 20) ;

    }
}
?>