<?php

class TimesheetRecord extends MappedObject
{
    public $taskid;
    public $userid;

    public $starttime;
    public $endtime;

    public $timesheetid=0;

    /**
     * The name of the task. It's pulled through occaisionally in
     * select joins, but don't rely on it being here.
     */
    private $tasktitle;
    
    /**
     * The category of the task
     */
    private $taskcategory;

    /** This field is pulled down in some selects only */
    private $projecttitle;
    private $clienttitle;

    private $clientid;
    private $projectid;

    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
    }
    
    public function setTaskCategory($val)
    {
        $this->taskcategory = $val;
    }
    
    public function getTaskCategory()
    {
        return $this->taskcategory;
    }

    public function setTaskTitle($val)
    {
        $this->tasktitle = $val;
    }

    public function getTaskTitle()
    {
        return $this->tasktitle;
    }

    public function setClientId($id)
    {
        $this->clientid = $id;
    }

    public function getClientId()
    {
        return $this->clientid;
    }

    public function setProjectId($id)
    {
        $this->projectid = $id;
    }

    public function getProjectId()
    {
        return $this->projectid;
    }


    public function setProjectTitle($t)
    {
        $this->projecttitle = $t;
    }

    public function setClientTitle($t)
    {
        $this->clienttitle = $t;
    }

    public function getProjectTitle()
    {
        return $this->projecttitle;
    }

    public function getClientTitle()
    {
        return $this->clienttitle;
    }
    /**
     * Get the amount of time to elapse before updating this entry.
     *
     * @return int
     */
    public function getUpdateTime()
    {
        return ProjectService::TASK_UPDATE_TIME;
    }


    /**
     * Delete this record and delete its duration from the task
     * TODO: Remove once this is ported!
     */
    public function deleteRecord()
    {
        $user = za()->getUser();
        $db = Zend::registry('DbService');
        $taskId = $this->taskid;

        $select = $db->select();
        $select->
        from('task', '*')->
        where('id = ?', $taskId);

        $task = $db->getObject($select, 'task');
        if ($task == null) {
            throw new Exception("Task ID ".$taskId." does not exist");
        }

        $toDelete = $this->endtime - $this->starttime;
        try {
            $db->delete($this, 'id='.$this->id);
            	
            // Get the total time for this task. $select = $db->select();
            $select = $db->select();
            $select->
            from('timesheetrecord', new Zend_Db_Expr('SUM(endtime - starttime) AS tasktime'))->
            where('taskid = ?', $taskId);
            	
            $row = $db->fetchAll($select);
            $total = $row[0]['tasktime'];
            if ($total > 0) {
                // hours = timespent / 3600
                $task->timespent = $task->timespent - $toDelete;
                $task->save();
            }
        } catch (Exception $e) {
            $this->log->err($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Get the duration of this timesheet entry as a
     * formatted string
     *
     * @param string $format The format is any valid date() format. It is
     * rooted at 0, so only the hour / minutes really are applicable.
     */
    public function getDuration($format="H:i:s")
    {
        // what's our current timezone offset (server wise..)
        $diff = ($this->endtime - $this->starttime);

        // If we're doing a timesheet record for months... well
        // lets think about the consecquences of that later.
        $days = gmdate("d", $diff) - 1;
        $hours = gmdate("H", $diff);
        $hours = $hours + $days * 24;
        $mins = gmdate("i", $diff);
        return $hours.":".$mins;
    }

}
?>