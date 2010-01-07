<?php

class TestUser extends UnitTestCase
{
    public function testRoles()
    {
        $user = new User();
        
        $user->role = User::ROLE_ADMIN;
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasRole(User::ROLE_ADMIN));
        
        $this->assertTrue($user->hasRole(User::ROLE_POWER));
        $this->assertTrue($user->hasRole(User::ROLE_USER));

        $user->role = User::ROLE_USER;
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertFalse($user->hasRole(User::ROLE_ADMIN));
    }
    

}

?>