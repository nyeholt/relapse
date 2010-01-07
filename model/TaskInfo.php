<?php

/**
 * This class isn't persisted, but is used as a container
 * for holding task information for reporting etc.
 *
 */
class TaskInfo
{
    public $title;
    public $taskid;
    public $taskcategory;
    
    public $user;
    public $client;
    public $project;
    
    public $projectid;
    public $clientid;
    public $projecttitle;
    public $clienttitle;
    
    public $days = array();
}
?>