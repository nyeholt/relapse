<?php

class GroupService
{
    public $dbService;
    
    /**
     * @var UserService
     */
    public $userService;
    
    /**
     * Create a group from the given group object.
     *
     * @param  Group $group
     */
    public function createGroup($params)
    {
        $existing = $this->dbService->getByField(array('title' => $params['title']), 'UserGroup');
        if ($existing) {
            throw new Exception("Group ".$group->title.' already exists');
        }
        return $this->dbService->saveObject($params, 'UserGroup');
    }
    
    public function getGroup($id)
    {
        return $this->dbService->getById((int) $id, 'UserGroup');
    }

    /**
     * Get the groups in the system
     */
    public function getGroups($where=array())
    {
        return $this->dbService->getObjects('UserGroup', $where, 'parentpath asc');
    }

    /**
     * Get a user by a particular field
     *
     * @param string $field
     * @param mixed $value
     * @return UserGroup
     */
    public function getGroupByField($field, $value)
    {
        return $this->dbService->getByField(array($field => $value), 'UserGroup');
    }

    /**
     * Add an object (either a group or a user)
     * to a group
     *
     * @param mixed $object
     * @param UserGroup $group
     */
    public function addToGroup(UserGroup $toGroup, $object)
    {
        if ($object instanceof User) {
            // create a new group membership
            try {
                $this->dbService->beginTransaction();
                $this->removeFromGroup($toGroup, $object);

                $groupMember = new GroupMember();
                $groupMember->userid = $object->id;
                $groupMember->groupid = $toGroup->id;
                if (!$this->dbService->createObject($groupMember)) {
                    throw new Exception("Failed creating membership");
                }
                 
                $this->dbService->commit();
            } catch (Exception $e) {
                $this->dbService->rollback();
                throw $e;
            }
        } else if ($object instanceof UserGroup) {
            // we simply update its parent path property
            $newPath = $toGroup->getPath();

            if (strpos($newPath, '-'.$object->id.'-') !== false) {
                throw new RecursiveGroupException("Cannot add group as a child of itself");
            }
            // $newPath = $toGroup->parentpath ? '.'.$toGroup->id : $toGroup->id;
            $object->parentpath = $newPath;
            $this->dbService->updateObject($object);
        } else {
            throw new Exception("Object ".get_class().' cannot be added to a group');
        }
    }

    /**
     * Remove an item from a group
     *
     * @param User $object
     * @param UserGroup $group
     */
    public function removeFromGroup(UserGroup $fromGroup, User $object)
    {
        // delete a group membership
        try {
            $this->dbService->beginTransaction();
            $this->dbService->delete('groupmember', 'userid='.$object->id.' AND groupid='.$fromGroup->id);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
        }
    }

    /**
     * Empties a group of all its users.
     *
     * @param UserGroup $fromGroup
     */
    public function emptyGroup(UserGroup $fromGroup)
    {
        try {
            $this->dbService->beginTransaction();
            $this->dbService->delete('groupmember', 'groupid='.$fromGroup->id);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
        }
    }

    /**
     * Add a bunch of users to a group
     *
     * @param UserGroup $group
     * @param array $userids
     */
    public function addUsersToGroup(UserGroup $group, array $userids)
    {
        $this->dbService->beginTransaction();
        $this->emptyGroup($group);
        foreach ($userids as $userid) {
            $this->log->debug("Adding user $userid to group $group->id");
            $user = $this->userService->getUser($userid);
            $this->addToGroup($group, $user);
        }
        $this->dbService->commit();
    }

    /**
     * Delete the given group, any group memberships, and
     * set any child group's parent to this group's parent
     *
     * @param UserGroup $group
     */
    public function deleteGroup($group)
    {
        $parent = $group->parentpath;
        // If it has children, we aren't going to delete it
        if ($this->dbService->getByField(array('parentpath'=>$group->getPath()), 'UserGroup')) {
            throw new NonEmptyGroupException("Group is not empty");
        }
        $this->dbService->beginTransaction();
        $this->dbService->delete($group);
        $this->dbService->delete('groupmember', 'groupid='.$group->id);
        $this->dbService->commit();
    }

    /**
     * Get the groups a user is in
     * 
     * Returns the list of groups indexed by the group id
     * 
     * @param $user
     * @return List<Group>
     */
    public function getGroupsForUser(User $user, $immediateOnly=true)
    {
        $uc = strtolower($this->userService->getUserClass());
        $select = $this->dbService->select();
        
        $select->from('usergroup', '*')
    	       ->joinInner('groupmember', 'usergroup.id=groupmember.groupid', 'usergroup.id as ugid')
    	       ->where('groupmember.userid = ?', $user->id);
     
        $objs = $this->dbService->fetchObjects('UserGroup', $select);
        
        if ($immediateOnly) {
            return $objs;
        }

        $return = new ArrayObject();
        // go and get all the ancestor groups
        foreach ($objs as $group) {
            $ancestors = $this->getAncestorGroups($group);
            $return[$group->id] = $group;
            foreach ($ancestors as $anc) {
                $return[$anc->id] = $anc;
            }
        }
        return $return;
    }
    
    /**
     * Get all the ancestor groups of the given group
     */
    public function getAncestorGroups(UserGroup $group)
    {
        $path = $group->getPath();
        
        if ($path == '-'.$group->id.'-') {
            return array($group);
        }
        
        $paths = split('-', $path);
        // split it up
        
        $pathsToGet = array();
        
        $in = '';
        for ($i = 0, $c = count($paths); $i < $c; $i++) {
            // if the path is empty, just skip it. 
            if (!$paths[$i] || $paths[$i] == $group->id) continue;

            // build the in clause
            $in .= ','.$paths[$i];
        }
        $in = ltrim($in, ','); 
        if (mb_strlen($in)) {
            $groups = $this->getGroups(array(new Zend_Db_Expr("id in ($in)")));
        } else {
            $groups = array($group);
        }

        return $groups;
    }

    /**
     * Get all the users in a given group
     *
     * @param UserGroup $group
     * @param boolean $immediateOnly whether to only get the immediate group members
     */
    public function getUsersInGroup(UserGroup $group, $immediateOnly=false)
    {
        // Okay, so we're after ANY group that starts with $group->parentpath.-id-
        $path = trim($group->getPath());
        $uc = strtolower($this->userService->getUserClass());
        $select = $this->dbService->select();

        if ($path == '') {
            $immediateOnly = true;
        }

        /* @var $select Zend_Db_Select */
        if ($immediateOnly) {
            $select->from('usergroup', 'id as __uid')
    	       ->joinInner('groupmember', 'usergroup.id=groupmember.groupid', 'usergroup.id as ugid')
    	       ->joinInner($uc, 'groupmember.userid='.$uc.'.id', '*')
    	       ->where('usergroup.id = ?', $group->id);
        } else {
            $select->from('usergroup', 'id as __uid')
    	       ->joinInner('groupmember', 'usergroup.id=groupmember.groupid', 'usergroup.id as ugid')
    	       ->joinInner($uc, 'groupmember.userid='.$uc.'.id', '*')
    	       ->where('usergroup.parentpath like ?', $path.'%')
    	       ->orWhere('usergroup.id = ?', $group->id);
        }
        
        $select->group($uc.'.id');
        
        $objs = $this->dbService->fetchObjects($this->userService->getUserClass(), $select);
        
        // Make sure there's only a single user per id
        
        return $objs;
    }
}

?>