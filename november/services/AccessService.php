<?php
include_once NOVEMBER_APP_DIR .'/model/ActionAccess.php';

class AccessService
{
    /* @var $dbService DbService */
    public $dbService;

    public function getAccessList($user='', $module='*', $controller='*')
    {
        
        $select = $this->dbService->select();
        $select->from('actionaccess');
        $where = array();
        
        if ($user !== '') {
            $where['username='] = $user;
        }
        
        if ($module !== '*') {
            $where['module='] = $module;
        }
        
        if ($controller !== '*') {
            $where['controller='] = $controller;
        }
        
        return $this->dbService->getObjects('ActionAccess', $where);
    }
    
    public function saveAccess(ActionAccess $access) 
    {
        return $this->dbService->saveObject($access);
    }
    
    public function deleteAccess(ActionAccess $access)
    {
        return $this->dbService->delete($access);
    }
}
?>