<?php
/**
 * This file belongs to the November framework, an extension of the
 * Zend Framework, written by Marcus Nyeholt <marcus@mikenovember.com>
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to marcus@mikenovember.com so I can send you a copy immediately.
 *
 * @package   November
 * @copyright  Copyright (c) 2006-2007 Marcus Nyeholt (http://mikenovember.com)
 * @version    $Id$
 * @license    New BSD License
 */

include_once dirname(__FILE__).'/RunnableTask.php';

class ScheduledTasksService
{
	/**
	 * @var TrackerService
	 */
	public $trackerService;
	
	/**
	 * If there's a notification service available, use it to email someone
	 *
	 * @var NotificationService
	 */
	public $notificationService;
	
    private static $DATA_FILE = 'scheduled_tasks_cache.php';
    
    private $lockFile; 
    
    private $jobData;
    
    private $lastRun = 0;
    private $isRunning = 0;
    
    public function __construct()
    {
        $this->lockFile = BASE_DIR.'/data/cache/__run_lock';
        
        $this->jobData = array();
        try {
			if (!Zend_Loader::isReadable(self::$DATA_FILE, BASE_DIR.'/data/cache')) {
				throw new Zend_Exception("Scheduled task cache not found");
			}
            Zend_Loader::loadFile(self::$DATA_FILE, BASE_DIR.'/data/cache', null, true);
            global $__SCHEDULE_CACHE;
            if (isset($__SCHEDULE_CACHE)) {
                $this->jobData = $__SCHEDULE_CACHE;
            } else {
                $this->jobData = array();
            }
        } catch (Zend_Exception $e) {
            // So create the cache dammit!
        }

        $this->lastRun = ifset($this->jobData, 'last_run', 0);
        

    }

    public function run()
    {
    	$lockCount = (int) $this->isLocked();
        if ($lockCount) {
            // can't run just yet
            $this->log->warn("Tasks are still running, please wait");
            
            // see how long it's been locked for and notify if necessary
			if ($lockCount >= 10 && ($lockCount % 10 == 0)) {
				// ugly hack for now....
				$notificationService = za()->getService('NotificationService');
				if ($notificationService) {
					$user = new User();
					$user->email = "alex@lateralminds.com.au";
					$msg = "Scheduled tasks have stopped running. Please check the logfile at relapse/data/logs/cron.log\r\n";
					$msg.= "You may need to delete the lock file at relapse/data/cache/__run_lock.";
					$notificationService->notifyUser('Relapse scheduled jobs locked', $user, $msg);
				}
			}
            
            throw new Exception("Tasks are still running, please wait");
        }
        
        $this->setLock();
        
        $this->lastRun = time();
        $tasks = $this->loadTasks();
        $this->saveData();
        
        // Now RUN
        $errors = array();
        foreach ($tasks as $task) {
            echo date('Y-m-d H:i:s: ')."Executing ".$task->getTaskName()."\n";
            try {
                $task->execute();
            } catch (Exception $e) {
                $errors[$task->getTaskName()] = $e;
            }
        }

        foreach ($errors as $name => $exc) {
            echo "Error executing $name: ".$exc->getMessage()."\n";
            $this->log->err("Failed executing task $name: ".$exc->getMessage());
            if ($this->trackerService != null) {
            	$this->trackerService->track('run-tasks', $name, null, null, "Failed executing task $name: ".$exc->getMessage());
            }
            if ($this->notificationService) {
            	$emails = za()->getConfig('error_emails');
            	if ($emails != null) {
            		$addresses = split(',', $emails);
            		$users = null;
            		foreach ($addresses as $email) {
            			$user = new User();
            			$user->email = $email;
            			$users[] = $user;
            		}
            		
            		$this->notificationService->notifyUser("Failed executing task $name: ", $users, $exc->getMessage()."\r\n\r\n".$exc->getTraceAsString());
            	}
            	
            }
            $this->log->err($exc->getTraceAsString());
        }
        
        // delete the lock file
        $this->clearLock();
    }
    
    /**
     * Run an explicitly named task, regardless of its next
     * run time
     */
    public function forceRun($taskName)
    {
    	$tasksClass = APP_DIR.'/tasks/'.$taskName.'.php';
    	if (file_exists($tasksClass)) {
    		include_once ($tasksClass);
    		$service = new $taskName;
            if (!$service instanceof RunnableTask) {
                continue;
            }
            za()->inject($service);
            $service->execute();
    	}
    }
    
    public function isLocked()
    {
		$locked = 0;
		if (file_exists($this->lockFile)) {
			$locked = file_get_contents($this->lockFile);
			if ($locked == null) {
				$locked = 0;
			}
			// update the lock count if it is actually locked
			file_put_contents($this->lockFile, ++$locked);
		}
        return $locked;
    }

    public function setLock()
    {
        touch($this->lockFile);
    }
    
    public function clearLock()
    {
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);            
        }
    }

    /**
     * Load the tasks to be run this run.
     */
    private function loadTasks()
    {
        $tasksDir = APP_DIR.'/tasks';
	    $toRun = new ArrayObject();
	    
        $dir = new DirectoryIterator($tasksDir);

        foreach ($dir as $value) {
            if ($value == '.' || $value == '..') continue;
            
            if (strpos($value, '.php') === false || strpos($value, '.') === 0) continue;
            
            $cls = substr($value, 0, strrpos($value, '.'));
            include_once $tasksDir.'/'.$value;
            $service = new $cls;
            if (!$service instanceof RunnableTask) {
                continue;
            }
            
            // Figure out when this task was last run and if it should be run 
            // this time around
            $name = $service->getTaskName();
            if (!mb_strlen($name)) {
                throw new Exception("Task for $cls does not have a name");
            }

            $info = ifset($this->jobData, $name, array());
            
            $lastRun = ifset($info, 'last_run', 0);
            
            $nextRun = 0;
            if ($service->getInterval() > 0) {
                // if it's an interval, nextRun = lastRun + interval
                $nextRun = $lastRun + $service->getInterval();
            } else {
                $nextRun = $service->getNextRun($lastRun);
            }
            
            if ($nextRun > 0) {
	            // Calculate whether we should run this task now
	            // or not. The '10' in here is to add a bit of padding in case
	            // we're not 1000000th of a second accurate in run intervals
	            if ((time()+10) >= $nextRun) {
	                $this->log->debug("Running task $name");
	                $info['last_run'] = time();
	                $this->jobData[$name] = $info;
	                za()->inject($service);
	                $toRun[] = $service;
	            } else {
	                $this->log->debug("Skipping task $name, next run at ".date('Y-m-d H:i:s', $nextRun));
	            }
            }
        }

        return $toRun;
    }

    private function saveData()
    {
        $this->jobData['last_run'] = $this->lastRun;
        
        $code = '<?php
        global $__SCHEDULE_CACHE;
        $__SCHEDULE_CACHE = '.var_export($this->jobData, true).';
        ?>';

        $cacheFile = BASE_DIR.'/data/cache/'.self::$DATA_FILE;
        $fp = fopen($cacheFile, "w");
        if ($fp) {
            fwrite($fp, $code);
            fclose($fp);
        }
    }
}

?>
