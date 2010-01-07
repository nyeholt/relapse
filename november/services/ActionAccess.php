<?php

class ActionAccess extends Bindable
{
    public $id;
    
    public $username;
    public $module;
    public $controller;
    
    /**
     * This should be "grant" or "deny"
     *
     * @var String
     */
    public $action;
    
    public $role;
}
?>