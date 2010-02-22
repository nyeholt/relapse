<?php

include_once 'model/UserGroup.php';
include_once 'Zend/Feed.php';

class AdminController extends NovemberController
{
	/**
	 * The admin service
	 *
	 * @var AdminService
	 */
	public $adminService;

	/**
	 * UserService
	 *
	 * @var UserService
	 */
	public $userService;

	/**
	 * ClientService
	 *
	 * @var ClientService
	 */
	public $clientService;

	/**
	 * @var GroupService
	 */
	public $groupService;

	/**
	 * @var AccessService
	 */
	public $accessService;

	/**
	 * @var TrackerService
	 */
	public $trackerService;

	public $allowedMethods = array(
	'updateAccessAction' => 'post',
	);

	/**
	 * Display the current configuration
	 *
	 */
	public function indexAction()
	{
		// Get the current configuration and
		// show a form for editing it
		$this->view->config = $this->adminService->getSystemConfig();
		$this->renderView('admin/config.php');
	}

	public function feedAction()
	{
		$entries = $this->trackerService->getEntries(array(), 1, 50);
   
		$feedArray = array(
		'title' => 'Relapse Updates',
		'link' => za()->getConfig('site_domain').za()->getConfig('site_context'),
		'description' => 'Latest relapse updates',
		'language' => 'en-us',
		'charset' => 'utf-8',
		'pubDate' => date(DATE_RFC822),
		'generator' => 'Zend Framework Zend_Feed',
		'entries' => array()
		);

		foreach ($entries as $entry) {
			// we want to handle issue history stuff differently otherwise we end up with oto
			// much crap in the feed
			$desc = $entry->entrydata;
			$feedArray['entries'][] = array(
				'title' => $entry->actionname. ':'.$entry->actionid,
				'link' => $entry->url,
				'guid' => $entry->id,
				'description' => $desc,
				'lastUpdate' => strtotime($entry->created),
			);
		}

		$feed = Zend_Feed::importArray($feedArray, 'rss');
		$feed->send();
   
	}

	public function saveconfigAction()
	{
		$config = $this->adminService->getSystemConfig();
		$params = $this->_getAllParams();
		$newConfig = array_merge($config, $params);

		$this->log->debug("Config updated to ".print_r($params, true));

		$this->adminService->saveConfig($newConfig);
		// exit();
		$this->redirect('admin');
	}

	/**
	 * List all users in the system (not admins, just users)
	 *
	 */
	public function userlistAction()
	{
		$users = $this->userService->getUserList(array(), false);

		$this->view->roles = za()->getUser()->getAvailableRoles();
		$this->view->users = $users;
		$this->renderView('admin/user-list.php');
	}

	public function changeroleAction()
	{
		$user = $this->userService->getUser((int) $this->_getParam('id'));

		if (!$user) {
			$this->flash("Cannot change role for that user");
			$this->redirect('admin', 'userlist');
		}

		$this->userService->setUserRole($user, $this->_getParam('role'));
		$this->redirect('admin', 'userlist');
	}

	/**
	 * The group list
	 *
	 */
	public function grouplistAction()
	{
		$model = $this->byId(null, 'UserGroup');
		if ($model == null) $model = new UserGroup();

		$this->view->model = $model;
		$this->view->groups = $this->groupService->getGroups();
		$this->renderView('admin/group-list.php');

	}

	/**
	 * Get the last few tracker items
	 *
	 */
	public function trackerAction()
	{
		$this->view->pagerName = 'tracker-page';

		$currentPage = ifset($this->_getAllParams(), $this->view->pagerName, 1);
		$this->view->totalEntries = $this->trackerService->getTotalEntries();
		// Get all projects

		$this->view->entries = $this->trackerService->getEntries(array(), $currentPage, 50);
		$this->renderView('admin/tracker.php');
	}

	/**
	 * Create a new group
	 *
	 */
	public function creategroupAction()
	{

		$this->saveAction('UserGroup');
	}

	/**
	 * Allow an empty 'parentpath'
	 *
	 * @param unknown_type $params
	 * @return unknown
	 */
	protected function filterParams($params)
	{
		return $params;
	}

	/**
	 * When deleting a group, need to delete all
	 * its children too.
	 *
	 */
	public function deletegroupAction()
	{
		$model = $this->byId(null, 'UserGroup');
		if ($model != null) {
			try {
				$this->groupService->deleteGroup($model);
			} catch (NonEmptyGroupException $neg) {
				$this->flash("Group is not empty and cannot be deleted");
			}
		}

		$this->redirect('admin', 'grouplist');
	}

	protected function onModelSaved($model)
	{
		$selectedParent = $this->byId($this->_getParam('parent'), 'UserGroup');
		if ($selectedParent != null) {
			try {
				$this->groupService->addToGroup($selectedParent, $model);
			} catch (RecursiveGroupException $rge) {
				$this->flash("Cannot set a child group as the parent");
			}
		}
		$this->redirect('admin', 'grouplist');
	}

	public function viewgroupAction()
	{
		$group = $this->byId($this->_getParam('id'), 'UserGroup');
		$users = $this->groupService->getUsersInGroup($group, true);

		$groupUsers = array();

		foreach ($users as $user) {
			$groupUsers[$user->id] = $user;
		}
		$this->view->groupusers = $groupUsers;
		$this->view->users = $this->userService->getUserList();
		$this->view->group = $group;
		$this->renderView('admin/view-group.php');
	}

	public function savegroupusersAction()
	{
		$usersToAdd = $this->_getParam('groupusers');
		if (!is_array($usersToAdd)) {
			$this->viewGroupAction();
			return;
		}

		$group = $this->byId($this->_getParam('id'), 'UserGroup');

		$this->groupService->addUsersToGroup($group, $usersToAdd);

		$this->redirect('admin', 'grouplist');
	}

	/**
	 * Action to list the access a user has to certain elements
	 *
	 */
	public function useraccessAction()
	{
		$username = $this->_getParam('username');
		if ($username == null) {
			$this->flash("Must provide username!");
			$this->redirect('admin');
			return;
		}
		$this->view->access = $this->accessService->getAccessList($username);
		$this->view->user = $this->userService->getUserByField('username', $username);
		$this->view->modules = za()->getConfig('modules');
		$this->view->modules[] = 'default';

		$this->renderView('admin/user-access.php');
	}

	public function updateaccessAction()
	{
		$username = $this->_getParam('username');
		$action = mb_strtolower($this->_getParam('doaction'));
		$item = $this->byId(null, 'ActionAccess');

		if ($item != null) {
			if ($action == 'update') {
				// make set values and quite
				$item->module = $this->_getParam('accessmodule');
				$item->action = $this->_getParam('accessaction');

				$this->accessService->saveAccess($item);
			} else {
				$this->accessService->deleteAccess($item);
			}
		} else {
			// Adding some new access
			$item = new ActionAccess();
			$item->username = $username;
			$item->module = $this->_getParam('accessmodule');
			$item->action = $this->_getParam('accessaction');
			$this->accessService->saveAccess($item);
		}

		$this->redirect('admin', 'useraccess', array('username' => $username));
	}
}
?>