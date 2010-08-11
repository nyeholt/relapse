<?php
class UserRole extends MappedObject
{
    const PERM_READ = 1;
    const PERM_WRITE = 2;
	const PERM_POWER = 4;
	const PERM_MANAGER = 8;
    const PERM_ADMIN = 16;

    const ROLE_READ = 'read';
    const ROLE_WRITE = 'write';
	const ROLE_POWER = 'power';
	const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';
    
    const GROUP = 'group';
    const USER = 'user';
    
    public $authority;
    public $role;
    public $itemtype;
    public $itemid;
    
    /**
     * Can be group or user
     *
     * @var string
     */
    public $type = self::USER;
    
    private static $roles = null;

    public $requiredFields = array('authority', 'role', 'itemtype', 'itemid');
    
    public function __construct() {
    }

    public static function getRole($role) {
        if (self::$roles == null) {
            self::$roles = array();
            self::$roles[self::ROLE_READ] = self::PERM_READ;
	        self::$roles[self::ROLE_WRITE] = self::PERM_READ + self::PERM_WRITE;
			self::$roles[self::ROLE_POWER] = self::$roles[self::ROLE_WRITE] + self::PERM_POWER;
			self::$roles[self::ROLE_MANAGER] = self::$roles[self::ROLE_POWER] + self::PERM_MANAGER;
			self::$roles[self::ROLE_ADMIN] = self::$roles[self::ROLE_MANAGER] + self::PERM_ADMIN;
        }

        return ifset(self::$roles, $role, 0);
    }
}
?>