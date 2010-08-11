<?php

include_once dirname(__FILE__).'/exceptions/ExistingUserException.php';
include_once dirname(__FILE__).'/exceptions/RecursiveGroupException.php';
include_once dirname(__FILE__).'/exceptions/NonEmptyGroupException.php';

include_once 'november/model/GroupMember.php';
include_once 'model/LeaveApplication.php';

/**
 * Stuff for managing users.
 *
 */
class UserService implements Configurable
{
    /**
     * The db service for retrieving objects
     *
     * @var DbService
     */
    public $dbService;

    /**
     * The auth service
     *
     * @var AuthService
     */
    public $authService;

    /**
     * The tracker service
     *
     * @var TrackerService
     */
    public $trackerService;

    /**
     * NotificationService
     *
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * @var AuthComponent
     */
    public $authComponent;

    /**
     * What's our user class?
     *
     * @var string
     */
    private $userClass = 'User';

    /**
     * Configure the service
     *
     * @param array $config
     */
    public function configure($config)
    {
        $this->userClass = ifset($config, 'user_class', 'User');
    }
    
    public function getUserClass()
    {
        return $this->userClass;
    }

    /**
     * Get a user by the given ID
     *
     * @param unknown_type $id
     * @return unknown
     */
    public function getUser($id)
    {
        return $this->dbService->getById($id, $this->userClass);
    }

    public function getByName($username)
    {
        return $this->getUserByField('username', $username);
    }

    /**
     * Get a user by a particular field
     *
     * @param string $field
     * @param mixed $value
     * @return User
     */
    public function getUserByField($field, $value)
    {
		if (!$value) {
			return null;
		}
        return $this->dbService->getByField(array($field => $value), $this->userClass);
    }

    /**
     * get a list of users matching a criteria
     *
     */
    public function getUserList($fields = array(), $excludeExternal = true)
    {
        // if $excludeExternal is true, then we don't include them in the listing
        if ($excludeExternal) {
            $fields['role<>'] = User::ROLE_EXTERNAL;
        }

        $users = $this->dbService->getObjects($this->userClass, $fields, 'username asc');
        return $users;
    }

    
    /**
     * Get a list of users for a particular client
     * 
     * This must bind against the 'user' table to make sure there's both a
     * user record AND a contact record under the given client name
     * 
     * @param String $clientName
     */
    public function getUsersForClient($id)
    {
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
        
        $select->from(mb_strtolower($this->userClass))
            ->joinInner('contact', mb_strtolower($this->userClass).'.contactid = contact.id')
            ->joinInner('client', 'contact.clientid = client.id')
            ->where('client.id=?', $id);
            
        $items = $this->dbService->fetchObjects($this->userClass, $select);
        return $items;
    }

    /**
     * Creates a new user
     *
     * @param  Array $params
     * @return The created user
     * @throws InvalidModelException
     * @throws ExistingUserException
     */
    public function createUser($params, $setAsAuthenticated = true, $role = User::ROLE_USER, $userType = null)
    {
        if ($userType == null) {
            $userType = $this->userClass;
        }
        // Check if there's a user with this email first.
        $select = $this->dbService->select();
        $select->from(strtolower($userType), '*')->
        where('username=?', $params['username'])->
        orWhere('email=?', $params['email']);
        $existing = $this->dbService->getObject($select, $userType);
        if ($existing) {
            throw new ExistingUserException($params['username'].' already exists');
        }

        $newPass = null;
        if (isset($params['password'])) {
            $newPass = $params['password'];
        }

        $params['role'] = $role;
        // Create a user with initial information
        $user = $userType;
        $user = new $user();

		$user->generateSaltedPassword($params['password']);
		unset($params['password']);

        $user->bind($params);

        $validator = new ModelValidator();
        if (!$validator->isValid($user)) {
            throw new InvalidModelException($validator->getMessages());
        }

        // so now we save the user, then reset their password which emails,
        // then we set them as the authenticated user.
        if ($this->dbService->createObject($user)) {
            $this->trackerService->track('create-user', $user->id);

            if ($setAsAuthenticated) {
                $this->authService->setAuthenticatedUser($user);
            }
            return $user;
        }

        return null;
    }

    /**
     * Update a given user.
     * @param NovemberUser $userToEdit
     * @param array $params
     */
    public function updateUser($userToEdit, $params, $synch=true)
    {
        if (isset($params['email'])) {
	        // Check if there's a user with this email first.
	        $existing = $this->dbService->getByField(array('email'=>$params['email']), $this->userClass);
	        if ($existing && $existing->id != $userToEdit->getId()) {
	            throw new ExistingUserException($params['email'].' already exists');
	        }
        }

        // Make sure no role is being changed! we do that in another method.
        unset($params['role']);

        $newPass = null;
        if (isset($params['password'])) {
            $newPass = $params['password'];
			$userToEdit->generateSaltedPassword($params['password']);
			unset($params['password']);
        }

        $userToEdit->bind($params);

        $validator = new ModelValidator();
        if (!$validator->isValid($userToEdit)) {
            throw new InvalidModelException($validator->getMessages());
        }
        $ret = $this->dbService->updateObject($userToEdit);
        $this->authComponent->updateUser($userToEdit, $newPass);
        return $ret;
    }
    
    public function saveUser($user)
    {
        $this->dbService->saveObject($user);
    }

    /**
     * Retrieves the amount of leave a given user has.
     */
    public function getLeaveForUser(User $user, $leaveType="Annual")
    {
        $leave = $this->dbService->getByField(array('username'=>$user->getUsername(), 'leavetype'=>$leaveType), 'Leave');
        if (!$leave && $user) {
            // Need to create new
            $params = array('username'=>$user->getUsername(), 'days'=>0, 'leavetype'=>$leaveType);
            $leave = $this->dbService->saveObject($params, 'Leave');
        }

        if (!$leave) {
            throw new Exception("Could not retrieve Leave details for ".$user->getUsername());
        }

        return $leave;
    }

    /**
     * Get all leave applications
     */
    public function getLeaveApplications($where=array(), $order='id desc', $page=null, $number=null)
    {
        return $this->dbService->getObjects('LeaveApplication', $where, $order, $page, $number);
    }

    public function getLeaveApplicationsCount($where=array())
    {
        return $this->dbService->getObjectCount($where, 'LeaveApplication');
    }

    /**
     * Get leave application for a single user
     */
    public function getLeaveApplicationsForUser(User $user)
    {
        return $this->getLeaveApplications(array('username=' => $user->getUsername()));
    }

    /**
     * Updates the amount of bonus leave a user has available
     */
    public function updateLeave(Leave $leave, $days)
    {
        $old = $leave->days;
        $leave->days = $days;

        $this->dbService->beginTransaction();
        $ret = $this->dbService->saveObject($leave);
        $this->trackerService->track('leave-updated', "$old changed to $days");
        $this->dbService->commit();
        return $ret;
    }

    /**
     * Figure out how many days of leave someone has based on
     * when they started.
     */
    public function calculateLeave(CrmUser $user)
    {
        $leavePerYear = za()->getConfig('days_leave', 20);
        $yearLength = 365.25;
        $timeInDays = strtotime($user->startdate);
         
        if (!$timeInDays) {
            return 0;
        }
        $timeInDays = time() - $timeInDays;

        return $timeInDays / 86400 / $yearLength * $leavePerYear;
    }

    /**
     * Make an application for leave
     */
    public function applyForLeave(User $user, $params)
    {
        $leave = $this->getLeaveForUser($user);
        if (!$leave) {
            throw new Exception("Could not create leave application");
        }

        $app = $this->saveLeaveApplication($params);
        if ($app) {
            // get all the usernames who are either admins or power users
            $approvers = $this->getApprovers();
            if (count($approvers)) {
                // Notify of the application
                $msg = new TemplatedMessage('new-leave-application.php', array('model'=>$app));
                $this->notificationService->notifyUser('New Leave Application', $approvers, $msg);
            }
        }

        return $app;
    }
    
    /**
     * Gets all the users that could be approvers
     */
    public function getApprovers()
    {
	    $admins = $this->getUserList(array('role='=>'Admin'));
	    $powers =  $this->getUserList(array('role='=>'Power'));
	    $approvers = array();
	    foreach ($admins as $admin) {
	        $approvers[] = $admin->username;
	    }
	    foreach ($powers as $power) {
	        $approvers[] = $power->username;
	    }
	    return $approvers;
    }

    public function saveLeaveApplication($params)
    {
        return $this->dbService->saveObject($params, 'LeaveApplication');
    }

    /**
     * Set the leave status
     */
    public function setLeaveStatus(LeaveApplication $leaveApplication, $status, $daysAffected = 0)
    {
        $leaveApplication->status = $status;

        $leaveApplication->approver = za()->getUser()->getUsername();
         
        if ($daysAffected) {
            $leaveApplication->days = $daysAffected;
        }

        $this->dbService->beginTransaction();
        
        if ($status == LeaveApplication::LEAVE_DENIED) {
            $leaveApplication->days = 0;
        } 
        
        $this->dbService->saveObject($leaveApplication);
        
    	if ($status == LeaveApplication::LEAVE_APPROVED) {
        	// if it's leave approved, need to create a task in the relevant project milestone
			// and make sure the user has time added for it
        }

        $this->applyTimeForLeave($leaveApplication);
        
        $this->trackerService->track('leave-updated', "Leave application for $leaveApplication->username set to $status");
        $this->dbService->commit();
         
        // send a message to the user
        $msg = new TemplatedMessage('leave-updated.php', array('model'=>$leaveApplication));
        $this->notificationService->notifyUser('Leave Application Updated', $leaveApplication->username, $msg);
    }
    
    /**
     * Get the task that represents the leave for a given leave application
     * and add some time to it for the given application
     */
    public function applyTimeForLeave(LeaveApplication $leaveApplication)
    {
    	$project = $this->projectService->getProject(za()->getConfig('leave_project'));
    	if (!$project) {
    		throw new Exception("Leave project not set correctly in configuration");
    	}
    	
    	$monthYear = date('F Y', strtotime($leaveApplication->to));
    	
    	$params = array('parentid='=>$project->id, 'title='=>$monthYear);
    	// get the appropriate milestone
		$projs = $this->projectService->getProjects($params);
		
		$milestone = null;
		if (count($projs)) {
			$milestone = $projs[0];
		} else {
			// create a new milestone 
			// $milestone 
			$date = date('Y-m-t', strtotime($leaveApplication->to)).' 23:59:59';
			$milestone = $this->projectService->createMilestone($project, $monthYear, $date);
			
		}
		
		// now get the task for the given leave app
		$taskTitle = $leaveApplication->leavetype.' Leave #'.$leaveApplication->id.': '.$leaveApplication->username.' '.date('Y-m-d', strtotime($leaveApplication->from)).' - '.date('Y-m-d', strtotime($leaveApplication->to));
		
		$params = array('projectid='=>$milestone->id, 'title='=>$taskTitle);
		$tasks = $this->projectService->getTasks($params);
		$user = $this->getUserByField('username', $leaveApplication->username);
		
		$task = null;
		if (count($tasks)) {
			$task = $tasks[0];
			// delete all timesheet entries for this user on this task
			
			$records = $this->projectService->getDetailedTimesheet($user, $task->id);
			foreach ($records as $record) {
				$this->projectService->removeTimesheetRecord($record);
			}
		} else {
			// create the new task
			$task = new Task();
			za()->inject($task);
			$task->title = $taskTitle;
			$task->projectid = $milestone->id;
			$task->category = 'Leave';
			$task->due = $leaveApplication->to;
			$task->description = $leaveApplication->reason;
			$task->estimated = za()->getConfig('day_length') * $leaveApplication->days;
			$task->complete = 1;
			
			$task = $this->projectService->saveTask($task);
			
		}
		
		if ($task != null) {
			// now add all the timesheet entries for each given day
			$startTime = strtotime(date('Y-m-d', strtotime($leaveApplication->from)).' 09:00:00');
			
			// now go through and add time for the given day
			for ($i = 0; $i < $leaveApplication->days; $i++) {
				// see if today's a weekend, if so we want to skip til the next monday
				$curDay = date('D', $startTime);
				if ($curDay == 'Sat') {
					$startTime += (2 * 86400);
				} else if ($curDay == 'Sun') {
					$startTime += 86400;
				}
				
				$endTime = $startTime + (za()->getConfig('day_length') * 3600);
				$this->projectService->addTimesheetRecord($task, $user, $startTime, $endTime);
				
				$startTime += 86400;
			}
		}
    }

    /**
     * Make a user an admin
     *
     * @param NovemberUser $user
     */
    public function setUserRole(CrmUser $user, $role=null)
    {
        if (!$role) $role = User::ROLE_USER;

        $user->role = $role;
        $this->trackerService->track('set-role', $role.'_user-'.$user->id.'_by-'.za()->getUser()->getId());
        $this->dbService->updateObject($user);
    }
}

?>