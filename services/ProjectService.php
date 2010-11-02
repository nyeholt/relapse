<?php

include_once 'model/Project.php';
include_once 'model/Task.php';
include_once 'model/TaskInfo.php';
include_once 'model/TimesheetRecord.php';
include_once 'model/ProjectStatus.php';
include_once 'services/exceptions/InvalidTimesheetRecordException.php';

class ProjectService {
    const TASK_UPDATE_TIME = 60;

    const MAX_RECORD_LENGTH = 7200;

    /**
     * DbService
     *
     * @var  DbService
     */
    public $dbService;
    
    /**
     * the userservice
     *
     * @var UserService
     */
    public $userService;
    
    /**
     * @var GroupService
     */
    public $groupService;
    
    /**
     * @var ItemLinkService
     */
    public $itemLinkService;
    
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var TrackerService
     */
    public $trackerService;
    
    /**
     * @var CacheService
     */
    public $cacheService;
    
    /**
     * Get a project
     *
     * @param int $id
     * @return Project
     */
    public function getProject($id)
    {
		if (!$id) {
			return null;
		}
        $proj = $this->dbService->getById($id, 'Project');
        
        if ($proj == null) return null;

        if (!$proj->ownerid || $proj->ownerid == ' ') {
			$group = $this->groupService->getGroupByField('title', za()->getConfig('issue_group'));
			if ($group && $group->id) {
				$proj->ownerid = $group->id;
				$this->saveProject($proj);
			}
        }

        return $proj;
    }

    /**
     * Get a project by a given field
     *
     * @param string $field
     * @param mixed $value
     */
    public function getProjectByField($field, $value)
    {
        return $this->dbService->getByField(array($field=>$value), 'Project');
    }
    
    /**
     * Gets a list of projects for a given client object, fully loaded with all 
     * sub projects and milestones too
     * 
     * @param $client
     * 			The client to get the list of child projects for
     * 
     */
    public function getProjectsForClient($client)
    {
    	$clientid = $client;
    	if (is_object($client)) {
    		$clientid = $client->id;
    	} 

    	$items = $this->cacheService->get($this->clientProjectsCacheKey($clientid));
    	if ($items !== null) {
    		return $items;
    	}

    	$projects = $this->getProjects(array('clientid='=>$clientid, 'parentid='=>0));
    	// now go through and load all sub projects too
		foreach ($projects as $project) {
			$this->initialiseProject($project);
		}
		// now we should be okay to cache this list out. 
		$this->cacheService->store($this->clientProjectsCacheKey($clientid), $projects, 1800); 
		 
		return $projects;
    }
    
    private function clientProjectsCacheKey($clientid)
    {
    	return 'client-'.$clientid.'-projects';
    }
    
    /**
     * Recursively prime the project object
     */
    private function initialiseProject(Project $project)
    {
    	// preload the project object
    	$children = $project->getChildProjects();
    	
    	// now do the same for children
		foreach ($children as $proj) {
			$this->initialiseProject($proj);
		}
    }
    
    /**
     * Get projects for a given client
     *
     * @param array $client
     */
    public function getProjects($where=array(), $order='title asc', $page=null, $number=null)
    {
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from('project', '*');
		 
		// $select = $this->dbService->applyWhereToSelect($where, $select);
		
		foreach ($where as $field => $value) {
		    if ($value instanceof Zend_Db_Expr) {
		        $select->where($value);
		    } else {
    			$select->where('project.'.$field.' ?', $value);
		    }
		}
		
		$select->joinInner('client', 'project.clientid=client.id', new Zend_Db_Expr('client.title as clienttitle'));

		$select->where('project.deleted=?', 0);
		$select->order($order);

		if (!is_null($page)) {
		    $select->limitPage($page, $number);
		}

		$projects = $this->dbService->fetchObjects('Project', $select);

		return $projects;
    }
    
	/**
     * Gets the list of letters that project names begin with.
     * 
     * Useful for UI related stuff, maybe other things too? 
     */
    public function getTitleLetters()
    {
        /* @var $select Zend_Db_Select */
        $query = "SELECT DISTINCT UPPER(LEFT(title,1)) as letter FROM project ORDER BY letter";
        
        $result = $this->dbService->query($query);
        
        $letters = array();
        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
	        $letters[] = $row['letter'];
	    }

	    return $letters;
    }
    
    /**
     * Save a project
     *
     * @param array|Project $params
     */
    public function saveProject($params)
    {
    	$this->dbService->beginTransaction();
    	// Double check to see whether the children projects are actually
		// a parent of the selected parent
		$pid = 0;
		$parent = 0;
		$project = null;
		$clientid = 0;
		
    	if (is_array($params)) {
    		$pid = ifset($params, 'id');
    		$parent = ifset($params, 'parentid');
    		if ($pid) {
	    		$project = $this->getProject($pid);
	    		$clientid = ifset($params, 'clientid', $project->clientid);
    		}
    	} else {
    		$pid = $params->id;
    		$parent = $params->parentid;
    		$project = $params;
    		$clientid = $project->clientid; 
    	}

   		if ($pid) {
   			// check the current parent client, if it's changed we need to update all our
			// child projects as well to let them know that the parent is now different
			$proj = $this->dbService->getById($pid, 'Project');
			$updateChildren = false;
			if ($proj->clientid != $clientid) {
				// update all the children too 
				$updateChildren = true;
			}
			
			// get all the children (including grandchildren and milestones)
			/* @var $project Project */
			$children = $project->getContainedMilestones();

			// see if the selected parent is in the list of children at all
			
			foreach ($children as $childProject) {
				if ($childProject->id == $parent) {
					throw new Exception("Cannot create recursive project hierarchy");
				}

				$this->log->debug("Updating project ".$childProject->title." to client $clientid");
				if ($updateChildren) {
					$childProject->clientid = $clientid;
					$this->dbService->saveObject($childProject, 'Project');
				}
			}
   		} else {
   			// we're creating a new project, so lets update the cache list
			$this->cacheService->expire($this->clientProjectsCacheKey($clientid));
   		}

		if ($params instanceof Project) {
			$this->updateProjectEstimate($params);
		}

        $savedProject = $this->dbService->saveObject($params, 'Project');
        
        // If this project's due date is greater than the parent's due date,
        // then update that parent's due date
        if ($savedProject && $savedProject->parentid) {
            $parentProject = $this->dbService->getById($savedProject->parentid, 'Project');
            $parentEnd = strtotime($parentProject->due);
            $thisEnd = strtotime($savedProject->due);
            if ($thisEnd > $parentEnd) {
                $parentProject->due = $savedProject->due;
                $this->saveProject($parentProject);
	            $group = $this->groupService->getGroup($parentProject->ownerid);
		        // Only send if the group exists
		        if ($group) {
		            $users = $this->groupService->getUsersInGroup($group);
		            $msg = new TemplatedMessage('project-end-updated.php', array('model'=>$parentProject));
		            try {
    		            $this->notificationService->notifyUser("Project due date changed", $users, $msg);
		            } catch (Exception $e) {
		                $this->log->warn("Failed sending project update email");
		            }
		        }
            }
            
            if (mb_strlen($savedProject->actualstart) && !mb_strlen($parentProject->actualstart)) {
            	$parentProject->actualstart = $savedProject->actualstart;
            	$this->saveProject($parentProject);
            }
        }

        $this->dbService->commit();

        return $savedProject;
    }
    
    /**
     * Create a subproject of the given project
     * 
     * @param Project $project  the project to create a child of.
     * @param String $newTitle the title of the sub-project
     * @param int $isMilestone whether the new child is actually a milestone
     * 
     * @return the created subproject 
     */
    public function createSubProject(Project $project, $newTitle, $asmilestone = 0)
    {
        $newProject = new Project();
        
        // make sure to inject!
        za()->inject($newProject);
        
        $newProject->title = $newTitle;
        $newProject->parentid = $project->id;
        $newProject->manager = $project->manager;
        $newProject->ownerid = null;
		$newProject->clientid = $project->clientid;
		$newProject->rate = $project->rate;
		$newProject->url = $project->url;
		$newProject->svnurl = $project->svnurl;
		$newProject->isprivate = $project->isprivate;
		$newProject->ismilestone = $asmilestone;

        // save the project 
        return $this->saveProject($newProject); 
    }
    
    /**
     * Create a milestone in a project
     * 
     * @param Project $project  the project to create a child of.
     * @param String $newTitle the title of the sub-project
     * 
     * @return the created milesonte
     */
    public function createMilestone(Project $project, $title, $dueDate)
    {
    	$milestone = $this->createSubProject($project, $title, true);
    	$milestone->due = $dueDate;
    	return $this->saveProject($milestone);
    }
    
    /**
     * Get a count for a project. 
     * 
     * @param array $where
     */
    public function getProjectCount($where=array())
    {
        $select = $this->dbService->select();
		$select->from('project', new Zend_Db_Expr('count(*) as total'));
		
		foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}

		$select->where('deleted=?', 0);

		$count = $this->dbService->fetchOne($select);

		return $count;
    }
    
    /**
     * Gets all the users assigned to a project
     *
     * @param Project $project
     * @return ArrayObject
     */
    public function getProjectUsers()
    {
        // For now, just return all users
        return $this->userService->getUserList();
    }

    /**
     * Delete a project
     *
     * @param Project $project
     */
    public function deleteProject(Project $project, $newParent = null)
    {
        $project->deleted = true;
        if ($newParent) {
        	// reassign all tasks, issues, features etc
			
        }
        return $this->dbService->updateObject($project);
    }
    
    /**
     * Get a saved project status object
     *
     * @param int $id
     * @return ProjectStatus
     */
    public function getStatus($id)
    {
        return $this->dbService->getById((int) $id, 'ProjectStatus');
    }

    public function saveStatus(ProjectStatus $status)
    {
        return $this->dbService->saveObject($status);
    }
    
    public function getStatusReports(Project $project)
    {
        return $this->dbService->getObjects('ProjectStatus', array('projectid='=>$project->id));
    }
    
    /**
     * Get an object for the given project status
     * 
     * @param Project $project
     * @return ProjectStatus
     */
    public function getProjectStatus(Project $project)
    {
        $projectStatus = new ProjectStatus();
        za()->inject($projectStatus);
        $projectStatus->projectid = $project->id;
        return $projectStatus;
    }
    
    /**
     * Get all the projects that a particular user belongs to
     * @param User $user
     * @return ArrayObject
     */
    public function getProjectsForUser(User $user)
    {
        $usersGroups = $this->groupService->getGroupsForUser($user);
        
        $in = '';
        foreach ($usersGroups as $group) {
            $in .= ','.$group->id;
        }
        $in = ltrim($in, ',');
        
        if (mb_strlen($in)) {
            $projects = $this->getProjects(array('ismilestone=' => 0, new Zend_Db_Expr("ownerid in ($in)")));
            return $projects;
        }
        
        return new ArrayObject();
    }
    
    
    /**************************
     * TASK STUFF
     *************************/
    
    /**
     * Get a task
     *
     * @param int $id
     * @return Task
     */
    public function getTask($id)
    {
        return $this->dbService->getById((int) $id, 'Task');
    }
    
    /**
     * Gets the project for storing unassigned tasks in
     * 
     * @return Project 
     */ 
    public function getUnassignedTaskProject()
    {
    	$company = za()->getConfig('owning_company');
    	
    	$project = $this->getProjectByField('title', 'Unassigned projects (#'.$company.')');
        if (!$project) {
            $params = array();
            $params['title'] = 'Unassigned projects (#'.$company.')';
            $params['clientid'] = $company; 
            $project = $this->saveProject($params);
        }
        
        $month = date('F Y');
        $projs = $this->getProjects(array('parentid=' => $project->id, 'title=' => $month));

        if (count($projs)) {
        	return $projs[0];
        } else { 
        	// create the milestone
			return $this->createMilestone($project, $month, date('Y-m-t 23:59:59'));
        // now get the milestone under that project for the current month
        }
    }
     
    /**
     * Save a task.
     * 
     * Saves the task object, then updates any task assignments that need 
     * attention. 
     * 
     * @param Task $taskToSave The task to save
     * @param boolean $updateGroups Whether to update group membership on save. Defaults
     * 								to false to prevent too much db access
     * @param boolean $updateAssignees Whether the assignees should be updated. 
     * 								make this false if you know the assigned users haven't changed
     */
    public function saveTask($taskToSave, $updateGroups = false, $updateAssignees = true)
    {
        $task = null;
        try {
        	
            $this->dbService->beginTransaction();
            $oldId = null;
            $projectid = 0;
            $title = '';
            if (is_array($taskToSave)) {
            	$title = ifset($taskToSave, 'title', 'Untitled Task');
                if (ifset($taskToSave, 'complete', false)) {
                    $taskToSave['completedate'] = date('Y-m-d H:i:s');
                }
                if (isset($taskToSave['id'])) {
                    $oldId = $taskToSave['id'];
                }
            } else {
            	$title = $taskToSave->title;
	            if ($taskToSave->complete) {
                    $taskToSave->completedate = date('Y-m-d H:i:s');
	            }
                if ($taskToSave->id) {
                    $oldId = $taskToSave->id;
                }
                
            }

            // if there's an OLD ID, get that task so we know what dependencies
            // will need to be updated after saving 
            $dependency = null;
            $oldState = null;
            
            if ($oldId) {
                $oldState = $this->getTask($oldId);
                $dependency = $oldState->getDependencyId();
            } else {
            	// no previous id, so must be creating from scratch	

				$this->trackerService->track('create-task', $title);
            }
            
            $task = $this->dbService->saveObject($taskToSave, 'Task');
            if ($task == null) {
                throw new Exception("Failed creating task for parameters ".print_r($taskToSave, true));
            } 
 
            $projectid = $task->projectid ? $task->projectid : 0;
            if (!$projectid) {
            	$unassignedProject = $this->getUnassignedTaskProject();
            	$task->projectid = $unassignedProject->id;
            	$this->dbService->saveObject($task);
            }
            // If the task's dependency is different, or its dates have changed,
            // update all dependants
            if ($oldState != null) {
                if ((date('Y-m-d', strtotime($oldState->due)) != date('Y-m-d', strtotime($task->due))) || $dependency != $task->getDependencyId()) {
                    // go and update the dependency
                    $this->updateTaskDependants($task, $dependency);
                }
            }
            
            // Get the project because we'll be adding users
            // to the project in a moment
            $project = $this->getProject($task->projectid);
            if (!is_array($task->userid)) {
                $task->userid = array();
            }

            if ($updateAssignees) {
	            // Delete all the old user/task assignments
	            $this->dbService->delete('usertaskassignment', 'taskid='.$task->id);
	            
	            foreach ($task->userid as $username) {
	                // create a new assignment
	                $params = array(
	                    'taskid' => $task->id,
	                    'userid' => $username,
	                    );
	                
	                $user = $this->userService->getByName($username);
	                if ($user && $updateGroups) {
	                    $groups = $this->groupService->getGroupsForUser($user, false);
	
	                    // Note here that ownerid == the group that owns this project
	                    if ($project->ownerid && !isset($groups[$project->ownerid])) {
	                        $group = $this->groupService->getGroup($project->ownerid);
	                        if ($group) {
	                            // Add the user to this group
	                            $this->groupService->addToGroup($group, $user);
	                            $this->log->debug(__CLASS__.':'.__LINE__.": User $user->username added to $group->title");
	                        } else {
	                            $this->log->warn(__CLASS__.':'.__LINE__.": Group not found for $project->ownerid");
	                        } 
	                    } else if ($project->ownerid) {
	                        $this->log->debug(__CLASS__.':'.__LINE__.": User $user->username is already in group ".$groups[$project->ownerid]->title);
	                    } else {
							$this->log->debug(__CLASS__.':'.__LINE__.": Project does not have an owner for assigning a group");
						}
	                } 
	
	                $this->dbService->saveObject($params, 'UserTaskAssignment');
	            }
            }
            
			$this->updateAffectedLinkedItems($task);
			
            $this->dbService->commit();
        } catch (InvalidModelException $ime) {
        	$this->log->err("Failed saving task because of invalid data: ".print_r($ime->getMessages(), true));
        	$this->log->err($ime->getTraceAsString());
            $this->dbService->rollback();
            throw $ime;
        } catch (Exception $e) {
            $this->log->err("Failed saving task ".$e->getMessage());
            $this->log->err($e->getTraceAsString());
            $this->dbService->rollback();
            throw $e;
        }

        return $task;
    }

    /**
     * For each task which is dependant on this task, make sure that their
     * start date is 1 day AFTER the start date of the given task. 
     * 
     * @param Task $task the task to update dependants of
     */
    public function updateTaskDependants(Task $task, $oldDepId = null)
    {
        $dependency = $oldDepId;
        $newDependency = $task->getDependencyId();
        if ($dependency == null) {
            $this->log->debug("Using task's new dependency");
            $dependency = $newDependency;
        }

        $this->log->debug("Updating tasks dependent on $dependency");

        // Update and set the start and enddates
        $dependants = $this->getTasks(array('dependency = ' => $dependency));
        foreach ($dependants as $dependantTask) {
            /* @var $dependantTask Task */
            $this->log->debug("Updating dates for task ".$dependantTask->title);
            
            $difference = strtotime($dependantTask->due) - strtotime($dependantTask->startdate);

            $newStartTime = strtotime($task->due) + 86400;
            $newDueDate = $newStartTime + $difference;
            
            $dependantTask->startdate = date('Y-m-d', $newStartTime);
            $dependantTask->due = date('Y-m-d', $newDueDate);
            $dependantTask->dependency = $newDependency;

            $this->saveTask($dependantTask, false, false);
        }
    }

    /**
     * Get the tasks from the given project that are still active
     *
     * @param Project $project
     */
    public function getActiveProjectTasks(Project $project)
    {
        return $this->getTasks(array('projectid=' => $project->id, 'complete='=>0));
    }

    /**
     * Get a list of tasks
     * @return ArrayObject
     */
    public function getTasks($where=array(), $order='due desc', $page=null, $number=null)
    {
        $tasks = $this->dbService->getObjects('Task', $where, $order, $page, $number);
        return $tasks;
    }

    /**
     * Get all the tasks for a given user
     *
     * @param unknown_type $user
     */

    public function getUserTasks($user, $where=array(), $order='due desc', $page=null, $number=null)  // $user, $incompleteOnly=true)
    {
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from('task', '*');
		$select->joinInner('usertaskassignment', 'usertaskassignment.taskid=task.id', 'taskid');
        $select->where('usertaskassignment.userid=?', $user->getUsername());

        $this->dbService->applyWhereToSelect($where, $select);
		
		$select->joinInner('project', 'project.id = task.projectid', 'project.title as projecttitle');
		
		$select->order($order);

		if (!is_null($page)) {
		    $select->limitPage($page, $number);
		}
		
		$tasks = $this->dbService->fetchObjects('Task', $select);
		return $tasks;
    }
    
    /**
     * Get the total number of tasks.
     *
     * @param unknown_type $where
     * @return unknown
     */
    public function getTaskCount($where = array())
    {
    	return $this->dbService->getObjectCount($where, 'Task');
    }
    
    /**
     * Gets the list of categories that a task can be
     * in.
     *
     */
    public function getTaskCategories()
    {
        $query = 'select distinct(category) from task';
        $result = $this->dbService->query($query);
        /* @var $result Zend_Db_Statement_Pdo */
        $res = $result->fetchAll(PDO::FETCH_COLUMN);
        return $res;
    }
    
    /**
     * Mark a task as being complete for the current user
     *
     * @param Task $task
     */
    public function completeTask(Task $task, User $user)
    {
        // Remove the user from the list of users. If afterwards there's
        // no users assigned, then we mark it as complete. 
        $index = array_search($user->username, $task->userid);
        if ($index === false) {
            return;
        }
        array_remove($task->userid, $index);

        if (count($task->userid) == 0) {
            $task->complete = true;
        }
        
        // remove the task from the watch list for the current user too
		$this->notificationService->removeWatch($user, $task->id, 'Task');

        $this->saveTask($task);
    }
    
    /**
     * Delete a task, and any residual timesheet entries
     * for it
     *
     */
    public function deleteTask(Task $task)
    {
        $success = false;
        try {
            $this->dbService->beginTransaction();
            // Delete from task
            $this->dbService->delete($task);
            // $success = $this->dbService->delete('timesheetrecord', 'taskid='.$task->id);
            $success = $success && $this->dbService->delete('usertaskassignment', 'taskid='.$task->id);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            throw $e;
        }
        
        return $success;
    }
    
    /**
     * Import tasks from a given file
     *
     * @param Project $project the project to import into 
     * @param string $file The file to import from
     * @param string $type the application used to export $file. Supports gp 
     * 						(GanttProject) and ms (ms project)
     */
    public function importTasks($project, $file, $type)
    {
        include_once dirname(__FILE__).'/lib/GanttProjectLibrary.php';
        include_once dirname(__FILE__).'/lib/MsProjectLibrary.php';
        
        $importer = null;
        
        switch ($type) {
            case 'gp': 
                $importer = new GanttProjectImporter();
                break;
            case 'ms': 
                $importer = new MsProjectImporter();
                break;
            default:
                throw new Exception("Unknown importer");
        } 
        
        za()->inject($importer);
        $errors = array();
        try {
            $this->dbService->beginTransaction();
            $errors = $importer->import($project, $file);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            $this->log->err("Failed importing tasks");
            throw $e;
        }
        
        return $errors;
    }
    
    /**
     * Get an exporter for exporting tasks
     * @param Project $project
     * @param string $type
     * @return TaskExporter
     */
    public function exportTasks($project, $type, $completed = false, $start=null, $end=null)
    {
        include_once dirname(__FILE__).'/lib/GanttProjectLibrary.php';
        include_once dirname(__FILE__).'/lib/MsProjectLibrary.php';
        
        $exporter = null;
        
        switch ($type) {
            case 'gp': 
                $exporter = new GanttProjectExporter();
                break;
            case 'ms': 
                $exporter = new MsProjectExporter();
                break;
            default:
                throw new Exception("Unknown importer");
        }
        
        $where = array('projectid=' => $project->id);
        
        if ($start != null) {
            $start = date('Y-m-d 00:00:00', strtotime($start));
            $where['created > '] = $start;
        }
        
        if ($end != null) {
            $end = date('Y-m-d 23:59:59', strtotime($end));
            $where['created < '] = $end;
        }
        
        if (!$completed) {
            $where['complete='] = 0;
        }

        $tasks = $this->getTasks($where);
        
        $exporter->setTasks($tasks);
        return $exporter; 
    }
    
    //////////////////////////////////////
    // TIMESHEET related stuff here     //
    //////////////////////////////////////

    /**
     * Gets all timesheet records for a given time period
     *  
     */
    public function getTimesheetData($from, $to)
    {
        $where = array(
            'created > ' => $from,
            'created < ' => $to,
        );
        
        return $this->dbService->getObjects('TimesheetRecord', $where);
    }

    /**
     * Get a timesheet
     *
     * @param int $id
     * @return Timesheet
     */
    public function getTimesheet($id)
    {
        return $this->dbService->getById((int)$id, 'Timesheet');
    }
    
    /**
     * Save a timesheet
     */
    public function saveTimesheet(Timesheet $timesheet)
    {
        return $this->dbService->saveObject($timesheet);
    }
    
    /**
     * Get a list of timesheets.
     *
     * @param unknown_type $where
     * @param unknown_type $order
     * @param unknown_type $page
     * @param unknown_type $number
     * @return unknown
     */
    public function getTimesheets($where=array(), $order='id asc', $page=null, $number=null)
    {
        return $this->dbService->getObjects('Timesheet', $where, $order, $page, $number);
    }
    
    /**
     * Get a timesheetrecord
     *
     * @param int $id
     * @return TimesheetRecord
     */
    public function getTimesheetRecord($id)
    {
        return $this->dbService->getById((int) $id, 'TimesheetRecord');
    }
    
    /**
     * Creates a new timesheet record 
     *
     * @param Task $task
     */
    public function addTimesheetRecord(Task $task, User $user, $start, $end)
    {
        if ($start > $end) {
            throw new InvalidTimesheetRecordException('Start is after end');
        }
        
        $task->start();

        $timeRecord = new TimesheetRecord();
		$timeRecord->starttime = $start;
		$timeRecord->endtime = $end;
		$timeRecord->taskid = $task->id;
		$timeRecord->userid = $user->getUsername();
		
		if ($this->dbService->createObject($timeRecord)) {
		    $this->updateTaskTime($task);
		    return $timeRecord;
		}
		return null;
    }
    
    /**
     * Removes a timesheet record from the system
     */
    public function removeTimesheetRecord(TimesheetRecord $record)
    {
        $task = $this->getTask($record->taskid);
        if (!$task) {
            throw new Exception("Cannot delete time from non-existent task");
        }
        $this->dbService->beginTransaction();
        $this->dbService->delete($record);
        $this->updateTaskTime($task);
        $this->dbService->commit();
    }
    

    /**
     * Update a project's estimated time
	 *
	 * This method does NOT save the project explicitly - it is assumed that
	 * our caller will do that.
     */
    public function updateProjectEstimate(Project $project)
    {
		if (!$project->id) {
			return;
		}
    	$estimate = 0;
    	// do we actually save this project and update its parents? 
    	$update = false;
    	// if it's a milestone, then update based on all of its tasks
    	if ($project->ismilestone) {
    		$allTasks = $project->getContainedTasks();
	        $estimate = 0;
	        foreach ($allTasks as $task) {
	        	$estimate += $task->estimated;
	        }
		
	        $taskestimate = $estimate ? $estimate / za()->getConfig('day_length', 8): 0;
	        if ($taskestimate && $taskestimate!=$project->taskestimate) {
	        	$project->taskestimate = $taskestimate;
	        	$update = true;
	        }
    	} else {
    		// otherwise, update based on the total of all of its direct children projects, 
			// plus any features attached at this project level
			
    		$select = $this->dbService->select();
	        /* @var $select Zend_Db_Select */
	
	        $select->from('feature', 'sum(estimated) as projectestimate');
	        $select->where('projectid=?', $project->id);
	        
	        $result = $this->dbService->query($select, array());
	        /* @var $result Zend_Db_Statement */
	        $row = $result->fetch(Zend_Db::FETCH_ASSOC);
	        
	        $estimate = ifset($row, 'projectestimate', 0);
	        
	        // now update based on all its children projects/milestones
			$children = $project->getChildProjects();
			$taskestimate = 0;
			$featureestimate = $estimate;
			foreach ($children as $child) {
				$taskestimate += $child->taskestimate;
				$featureestimate += $child->featureestimate;
			}
			
			if ($taskestimate && $taskestimate != $project->taskestimate) {
				$project->taskestimate = $taskestimate;
				$update = true;
			}
			if ($featureestimate && $featureestimate != $project->featureestimate) {
				$project->featureestimate = $featureestimate;
				$project->estimated = $featureestimate * za()->getConfig('day_length', 8);
				$update = true;
			}
    	}
    	
    	// Now, get all the time for this project
		$summary = $this->getSummaryTimesheet(null, null, $project->id, null, -1, '2000-01-01 00:00:00', '2100-01-01 00:00:00');
		$taken = 0;
		foreach ($summary as $task) {
			$taken += $task->timespent;
		}

		$grandchildren = $project->getAllSubProjects();
		foreach ($grandchildren as $grandkid) {
			$taken += $grandkid->currenttime;
		}

		$taken = $taken > 0 ? $taken / 3600 : 0;
 
		if ($taken > 0 && $project->currenttime != $taken) {
			$project->currenttime = $taken;
			// note that we do NOT save this here; we're assuming our caller will
			// save when appropriate
			$update = true;
		}

		if ($update) {
			// get its parent and update that too
			if ($project->parentid/* && $project->ismilestone*/) {
				$parent = $this->getProject($project->parentid);
				$this->saveProject($parent);
			}
		}
    } 
    
    /**
     * Update the time for a given task
     *
     * @param Task $task
     */
    private function updateTaskTime(Task $task)
    {
    	try {
	    	$this->dbService->beginTransaction();
	        // Get the total time for this task. $select = $dbService->select();
			$select = $this->dbService->select();
			$select->
				from('timesheetrecord', new Zend_Db_Expr('SUM(endtime - starttime) AS tasktime'))->
				where('taskid = ?', $task->id);

			$row = $this->dbService->fetchAll($select); 
			$total = $row[0]['tasktime'];
			if ($total > 0) {
	    		// hours = timespent / 3600
	    		$task->timespent = $total;
	    		$task->updated = date('Y-m-d H:i:s');
	    		
	    		// make sure that the project the task is attached to is marked as 'started'
				$project = $this->getProject($task->projectid);
				if ($project) {
					// make sure it has a start date
					if (!mb_strlen($project->actualstart)) {
						$project->actualstart = date('Y-m-d H:i:s', time());
						
					}

					// save to force a time update
					$this->saveProject($project);
				}

	    		$this->saveTask($task);
	    		// $this->dbService->updateObject($task);
			} else {
				// just save it that there's no time at all
				$task->timespent = 0;
				$this->saveTask($task);
			} 
			
			$this->dbService->commit();

    	} catch (Exception $e) {
    		$this->dbService->rollback();
    		throw $e;
    	}
    }
    
    /**
     * Update all linked items to the given task to make sure
     * their dates are as accurate as possible
     * 
     */
    private function updateAffectedLinkedItems(Task $task)
    {
    	// first get all issues
		$issues = $this->itemLinkService->getLinkedItemsOfType($task, 'to', 'Issue');
		
		foreach ($issues as $issue) {
			$tasks = $this->itemLinkService->getLinkedItemsOfType($issue, 'from', 'Task');
			$estimated = 0;
			$elapsed = 0;
			foreach ($tasks as $linkedTask) {
				/* @var $linkedTask Task */
				$estimated += $linkedTask->estimated;
				$elapsed += $linkedTask->timespent; 
			}

			// Convert elapsed to hours to match estimated time
			if ($elapsed > 0) {
				$elapsed = $elapsed / 3600;
			}

			// update the issue's time spent, but NOT its estimate - this is
			// separate from the task estimates. 
			// $issue->estimated = $estimated;
			$issue->elapsed = $elapsed;
			$this->dbService->saveObject($issue);
		}
		
		$features = $this->itemLinkService->getLinkedItemsOfType($task, 'to', 'Feature');

		foreach ($features as $feature) {
			$tasks = $this->itemLinkService->getLinkedItemsOfType($feature, 'from', 'Task');
			
			$estimated = 0;
			$elapsed = 0;
			foreach ($tasks as $linkedTask) {
				/* @var $linkedTask Task */
				$estimated += $linkedTask->estimated;
				$elapsed += $linkedTask->timespent; 
			}

			// Convert elapsed to hours to match estimated time
			if ($elapsed > 0) {
				$elapsed = $elapsed / 3600;
			}
			// update the feature's elapsed hours
			$feature->hours = $elapsed;
			$this->dbService->saveObject($feature);
		}
    }

    /**
     * Update a timesheet record
     * 
     * First off, we make sure that the passed in record
     * is updateable by checking that the given endtime matches
     * that stored in the DB
     * 
     * Then, check to see whether the start / end dates are greater
     * than a certain number of minutes (eg we don't want a single record
     * to have more than 8 hours of time on it so that reporting
     * doesn't get too out of alignment). 
     * 
     * Then, update the timesheet record and save the data
     *
     * @param TimesheetRecord $record
     * @param int $lastEndTime The end time of the last entry, to make suer that we've got a 
     * 							valid timing situation
     */
    public function updateTimesheetRecord(TimesheetRecord $record, $lastEndTime)
    {
        // Make sure it's the right record.
        $select = $this->dbService->select();
		$select->
			from('timesheetrecord', '*')->
			where('id = ?', $record->id)-> 
			where('endtime = ?', $lastEndTime);
		
		$verifyRecord = $this->dbService->getObject($select, 'TimesheetRecord');
		
		if ($verifyRecord == null) {
			throw new InvalidTimesheetRecordException("Record id ".$record->id." does not exist with endtime $lastEndTime");
		}
		
		// Get the task this record is associated with so we can update its elapsed time
		$task = $this->getTask($record->taskid);

		if (!$task) {
		    throw new Exception("Task for record does not exist");
		}
		
		$newTime = $record->endtime + self::TASK_UPDATE_TIME;
		// limit timesheet records to being 4 hours maximum
		if ($newTime - $record->starttime > (self::MAX_RECORD_LENGTH)) {
		    // create a new record instead of updating the old
            $newRecord = $this->addTimesheetRecord($task, za()->getUser(), $record->endtime, $newTime);
            return $newRecord;
		}

		// If the record has been locked (ie someone's locked this while
        // the user's actively timing it), create a new record
        if ($record->timesheetid > 0) {
            $newRecord = $this->addTimesheetRecord($task, za()->getUser(), $record->endtime, $newTime);
            return $newRecord;
        }

		$record->endtime = $newTime;

		try {
		    // start a transaction
		    $this->dbService->beginTransaction();
		    $this->dbService->updateObject($record);
		    $this->updateTaskTime($task);
		    $this->dbService->commit();
		    
		    return $record;
		} catch (Exception $e) {
		    $this->dbService->rollback();
		}
    }
    
    /**
     * Lock a timesheet and all of the timesheet entries recorded
     * from its start -> finish period. If there's an entry 
     * recorded during that period that belongs to another timesheet,
     * it won't be included.
     * 
     * @param Timesheet $timesheet the timesheet that is to be locked
     */
    public function lockTimesheet(Timesheet $timesheet)
    {
        $clientid = $timesheet->clientid;
        if ($timesheet->projectid) {
            $clientid = null;
        }
        $start = date('Y-m-d 00:00:00', strtotime($timesheet->from));
        $end = date('Y-m-d 23:59:59', strtotime($timesheet->to));
        $cats = array();
        if (is_array($timesheet->tasktype)) {
        	$cats = $timesheet->tasktype;
        } 
        
        $records = $this->getDetailedTimesheet(null, null, $timesheet->projectid, $clientid, null, $start, $end, $cats);
        
        try {
	        $this->dbService->beginTransaction();
	        foreach ($records as $record) {
	            if ($record->timesheetid > 0) {
	                za()->log("Record #$record->id is already part of timesheet #$record->timesheet");
	                continue;
	            }
	            $record->timesheetid = $timesheet->id;
	            $this->dbService->saveObject($record);
	        }

	        $timesheet->locked = 1;
	        $this->saveTimesheet($timesheet);
	        
	        $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            throw $e; 
        }
    }
    
    /**
     * As above, this goes through and sets all timesheet entries 
     * that have been locked against a given timesheet to being
     * unlocked
     *
     * @param Timesheet $timesheet
     */
    public function unlockTimesheet(Timesheet $timesheet)
    {
        try {
            $this->dbService->beginTransaction();
            
            $this->dbService->update('timesheetrecord', array('timesheetid'=>0), 'timesheetid='.(int) $timesheet->id);
            $timesheet->locked = 0;
            $this->saveTimesheet($timesheet);
            $this->dbService->commit();
        } catch (Exception $e) {
            $this->dbService->rollback();
            throw $e;
        }
    }
    
    /**
     * Gets an array of tasks formatted for a report type thing...
     * 
     * $taskinfo['user']['tasktitle']['for day'] = time spent
     */
    public function getTimesheetReport($user=null, $project=null, $client=null, $timesheet=-1, $start=null, $end=null, $categories=array(), $order='starttime asc')
    {
        $records = $this->getDetailedTimesheet($user, null, $project ? $project->id : null, $client ? $client->id : null, $timesheet, $start, $end, $categories, $order);
        
        // The following is $taskInfo['title']['Mon'|'Tue'|'etc'] = time spent
        $taskInfo = array();
        
        foreach ($records as $record) {
            /* @var $record TimesheetRecord */
            $index = $record->userid.'-'.$record->getTaskTitle().'-'.$record->getProjectId();
            $info = ifset($taskInfo, $index, new TaskInfo());
            
            $info->title = $record->getTaskTitle();
            $info->taskid = $record->taskid;
            $info->taskcategory = $record->getTaskCategory(); 

            $info->user = $record->userid;
            $info->clientid = $record->getClientId();
            $info->projectid = $record->getProjectId();
            $info->clienttitle = $record->getClientTitle();
            $info->projecttitle = $record->getProjectTitle();
            
            $info->client = $client ? $client : null;
            $info->project = $project ? $project : null;
            
            $day = date('D d/m', $record->starttime);
            $endDay = date('D d/m', $record->endtime);
            
            $timeForDay = $record->endtime - $record->starttime;
            if (!$timeForDay) continue;
            // If start and end don't match, take the little bit of end and
            // add it to the NEXT day. 
            // 17/7/07 Actually, this doesn't really matter
            // anymore; leave it all on the one day. 
            /*if ($endDay != $day) {
                $fakeEnd = strtotime(date('Y-m-d 00:00:00', $record->endtime));
                $extraBit = $record->endtime - $fakeEnd;
                $endDay = date('D d/m', $record->endtime);
                $endDayTime = ifset($info->days, $endDay, 0);
                $info->days[$endDay] = $endDayTime + $extraBit;
                $timeForDay -= $extraBit;
            }*/

            $dayTime = ifset($info->days, $day, 0);
            $info->days[$day] = $dayTime + $timeForDay;
            
            // To make sure it's unique for a user!!
            $taskInfo[$index] = $info;
        }
        
        return $taskInfo;
    }
    
    /**
     * Get a detailed timesheet
     * 
     * If an explicit timesheet id is passed in, only records that have
     * been locked off against that timesheet will be included in the
     * result. 
     *
     * @param NovemberUser $user
     * @param int $taskid
     * @param int $projectid
     * @param int $clientid
     * @param int $timesheet
     * @param int $start
     * @param int $end
     * @return ArrayObject
     */
    public function getDetailedTimesheet($user=null, $taskid=null, $projectid=null, $clientid=null, $timesheet=-1, $start=null, $end=null, $categories=array(), $order='endtime DESC')
	{
		$select = $this->dbService->select();
		/* @var $select Zend_Db_Select */
		$select->
				from('timesheetrecord', '*')->
				joinLeft('task', new Zend_Db_Expr('task.id=timesheetrecord.taskid'), new Zend_Db_Expr('task.title as tasktitle, task.category as taskcategory')); //->
		
		if (count($categories)) {
			$this->dbService->applyWhereToSelect(array('task.category' => $categories), $select);
		}

		$select = $this->filterBaseTimesheetQuery($select, $taskid, $projectid, $clientid, $start, $end);
		
		// If passed a user object, just filter their timesheet entries
		if ($user != null) {
			$select->where('timesheetrecord.userid = ?', $user->getUsername());
		}

		// If a timesheet is -1, it means we're just generating dynamic reports, 
        // and it doesn't matter if we include the already accounted for timesheet
        // records
	    if ($timesheet >= 0) {
    		$select->where('timesheetrecord.timesheetid = ?', $timesheet);		    
		}

		$select->order($order);

		return $this->dbService->fetchObjects('TimesheetRecord', $select);
	}

	/**
	 * Get a summary of task timing for a given period
	 *
	 * @param RegisteredUser $user
	 * @param int $taskid
	 * @param int $projectid
	 * @param int $clientid
	 * @param time $start
	 * @param time $end
	 * @return ArrayObject
	 */
	public function getSummaryTimesheet($user=null, $taskid=null, $projectid=null, $clientid=null, $timesheet=-1, $start=null, $end=null)
	{
		
		$select = $this->dbService->select()->
			from('task', array(new Zend_Db_Expr('task.title as title'), 'id'))->
			joinLeft('crmuser', 'task.userid=crmuser.username', 'username')->
			joinLeft('timesheetrecord', 'task.id=timesheetrecord.taskid', new Zend_Db_Expr('SUM(endtime - starttime) as timespent'));
		
		$select = $this->filterBaseTimesheetQuery($select, $taskid, $projectid, $clientid, $start, $end);
		// If we weren't passed a user, just load
		// one from the request
		if ($user != null) {
			$select->where('task.userid = ?', $user->getUsername());
		}
		
		if ($timesheet >= 0) {
    		$select->where('timesheetrecord.timesheetid = ?', $timesheet);		    
		}
		
		$select->group(new Zend_Db_Expr('task.id'));
		$select->order('endtime DESC');

		$tasks = $this->dbService->fetchObjects('task', $select);
		
		return $tasks;
	}
	
	/**
	 * Builds the basic select statement needed for filtering
	 * out which timesheet records we're interested in. 
	 *
	 * @param $select the base select to add date filters to
	 * @return Zend_Db_Select
	 */
	private function filterBaseTimesheetQuery($select, $taskid=null, $projectId=null, $clientId=null, $start=null, $end=null)
	{
		if ($start) {
    		if (!preg_match ('|[0-9]+-[0-9]+-[0-9]+ [0-9]+:[0-9]+:[0-9]+|', $start)) {
    		    $start = (date('Y-m-d 00:00:00', strtotime($start)));
    	    }
		}
		
		if ($end) {
    	    if (!preg_match ('|[0-9]+-[0-9]+-[0-9]+ [0-9]+:[0-9]+:[0-9]+|', $end)) {
    	        $end = (date('Y-m-d 23:59:59', strtotime($end)));
    	    }
		}
		
		// Filter for the project and client. 
		if ($projectId && !$clientId) {
			$select->joinInner('project', 'project.id=task.projectid', array(new Zend_Db_Expr('project.title as projecttitle'), new Zend_Db_Expr('project.id as projectid')));
			$select->joinLeft('client', 'client.id=project.clientid', array(new Zend_Db_Expr('client.title as clienttitle'), new Zend_Db_Expr('client.id as clientid')));
			// need to get this project and all its children
			$project = $this->getProject($projectId);
			$ids = $project->getChildIds(true, null);
			$ids[] = $project->id;
			$this->dbService->applyWhereToSelect(array('project.id' => $ids), $select);
			 
			// $select->where('project.id = ?', $projectId);
		}
		
		
		if ($clientId) {
			$select->joinInner('project', 'project.id=task.projectid', array(new Zend_Db_Expr('project.title as projecttitle'), new Zend_Db_Expr('project.id as projectid')));
			$select->joinInner('client', 'client.id=project.clientid', array(new Zend_Db_Expr('client.title as clienttitle'), new Zend_Db_Expr('client.id as clientid')));
			$select->where('client.id = ?', $clientId);
		}
		
		// If no IDs given, still join to get the relevant titles
		if (!$projectId && !$clientId) {
		    $select->joinLeft('project', 'project.id=task.projectid', array(new Zend_Db_Expr('project.title as projecttitle'), new Zend_Db_Expr('project.id as projectid')));
		    $select->joinLeft('client', 'client.id=project.clientid', array(new Zend_Db_Expr('client.title as clienttitle'), new Zend_Db_Expr('client.id as clientid')));
		}
		
		// Here we add in the clauses for limiting based
        // on the dates. The rules around this are that
        // if the timesheet record STARTS AFTER the selected start
        // time, or the record STARTS BEFORE the selected end
        // time. That allows us to grab records that straddle  
        // tricky points (such as those that live either
        // side of a day cut off point). 
        // The 4 hour limit in record length prevents the chance
        // of this allowing something like 28 hour days. 
		if ($taskid != null) {
		    // If for one individual task, add the id to limit the query
			$select->where('taskid = ?', $taskid);
			$view->showTask = false;
			
			if ($start) {
			    $start = strtotime($start);
			    // $select->where('timesheetrecord.endtime > ?', $start);
				$select->where('timesheetrecord.starttime > ?', $start);
			}
			if ($end) {
			    $end = strtotime($end);
				//$select->where('timesheetrecord.endtime < ?', $end);
				$select->where('timesheetrecord.starttime < ?', $end);
			}
			
		} else {
			if (!$start) {
				// beginning of today
				$time = time();
				$start = date('Y-m-d 00:00:00', $time);
			}
			
			if (!$end) {
				// End of today
				$time = time();
				$end = date('Y-m-d 23:59:59', $time);
			}
			$start = strtotime($start);
			$end = strtotime($end);
			$select->where('timesheetrecord.starttime > ?', $start)->
			where('timesheetrecord.starttime < ?', $end);
			// where('timesheetrecord.endtime < ?', $end);
		}
		
		return $select;
	}
	
	//////////////////////////////////////
    // FEATURE related stuff here     //
    //////////////////////////////////////
    
    /**
     * Get a feature by id
     *
     * @param int $id
     * @return Feature
     */
    public function getFeature($id)
    {
        return $this->dbService->getById($id, 'Feature');
    }
    
    public function saveFeature($feature)
    {
        $this->dbService->updateObject($feature);
    }
}
?>
