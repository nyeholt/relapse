<?php
include_once 'model/ItemWatch.php';

class NotificationService
{
	/**
	 * The user service
	 *
	 * @var UserService
	 */
	public $userService;

	/**
	 * The db service (hey we're lazy here...)
	 *
	 * @var DbService
	 */
	public $dbService;

	/**
	 * Notify the user (or users) about something
	 *
	 * @param string $subject
	 * @param array|User $user
	 * @param string $msg
	 */
	public function notifyUser($subject, $users, $msg, $from=null, $fromname=null, $html = false)
	{
		include_once 'Zend/Mail.php';
		
		if (!is_array($users) && !($users instanceof ArrayAccess)) {
			$users = array($users);
		}

		foreach ($users as $u) {
			if (!($u instanceof NovemberUser)) {
				$this->log->debug("Getting user for ".var_export($u, true));
				$u = $this->userService->getUserByField('username', $u);
			}
			if ($u == null) {
				$this->log->debug("Tried sending to non-existent user");
				continue;
			}

			$mail = new Zend_Mail();
			 
			if ($from == null) {
				$mail->setFrom(za()->getConfig('from_email'), za()->getConfig('name'));
			} else {
				if (!$fromname) $fromname = za()->getConfig('name');
				$mail->setFrom($from, $fromname);
			}

			$message = null;
			if ($msg instanceof TemplatedMessage) {
				$msg->model['user'] = $u;
				$message = $this->generateEmail($msg->template, $msg->model);
			} else {
				$message = $msg;
			}

			if ($html) {
				$mail->setBodyHtml($message);
			} else {
				$mail->setBodyText($message);
			}

			$mail->addTo($u->email, $u->username);
			$mail->setSubject($subject);
			 
			try {
				$this->log->debug("Sending message '$subject' to ".$u->email);
				$mail->send();
			} catch (Zend_Mail_Transport_Exception $e) {
				$this->log->debug(__CLASS__.':'.__LINE__." Failed sending mail: ".$e->getMessage());
				throw $e;
			}
		}
	}

	/**
	 * Add a note to an item
	 *
	 * @param unknown_type $toItem
	 * @param unknown_type $note
	 * @param unknown_type $title
	 */
	public function addNoteTo($toItem, $note, $title=null, $username=null)
	{
		// get rid of multiple Re: bits in a title
		$title = preg_replace('|(Re: )+|i', 'Re: ', $title);
		$params = array(
		'title' => $title,
		'note' => $note,
		'attachedtotype' => get_class($toItem),
		'attachedtoid' => $toItem->id,
		'userid' => $username == null ? za()->getUser()->getUsername() : $username,
		);

		return $this->dbService->saveObject($params, 'Note');
	}

	/**
	 * Gets the notes for a given item
	 */
	public function getNotesFor($type, $id=null, $order='created asc')
	{
		if (is_object($type)) {
			$id = $type->id;
			$type = get_class($type);
		}
		$select = $this->dbService->select();
		$select->from('note', '*')->
		where('attachedtotype = ?', $type)->
		where('attachedtoid = ?', $id);

		$select->order($order);
		return $this->dbService->fetchObjects('Note', $select);
	}

	/**
	 * Get a list of notes.
	 *
	 * @return  ArrayObject
	 */
	public function getNotes($where=array(), $order='created asc', $page=null, $number=null)
	{
		$select = $this->dbService->select();
		/* @var $select Zend_Db_Select */
		$select->from('note', '*');

		foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}

		if (!is_null($page)) {
			$select->limitPage($page, $number);
		}

		$select->order($order);

		$items = $this->dbService->fetchObjects('Note', $select);

		return $items;
	}

	/**
	 * Get just the note threads (ie for each attachedtoid and type)
	 */
	public function getNoteThreads($where=array(), $order='created desc', $page=null, $number=null)
	{
		$select = $this->dbService->select();
		/* @var $select Zend_Db_Select */
		$select->from('note', '*');

		foreach ($where as $field => $value) {
			$select->where($field.' ?', $value);
		}

		if (!is_null($page)) {
			$select->limitPage($page, $number);
		}

		$select->order($order);
		$select->group(array('attachedtoid', 'attachedtotype'));

		$items = $this->dbService->fetchObjects('Note', $select);

		return $items;
	}

	/**
	 * Get the number of note threads in the system
	 *
	 * @param array $where
	 * @return int
	 */
	public function getNoteThreadCount($where=array())
	{
		$sql = "SELECT count(*) as total FROM `note` where id in
        (select id from note GROUP BY `attachedtoid`,`attachedtotype`)";

		$result = $this->dbService->query($sql);
		/* @var $result Zend_Db_Statement_Pdo */

		$row = $result->fetch();

		return $row['total'];
	}

	/**
	 * Send notifications to all people who have a watch
	 *
	 * @param int $id
	 * @param string $type
	 * @param Note $note
	 */
	public function sendWatchNotifications(Note $note, $toUrl = null)
	{
		// Get all the usernames that match up
		$select = $this->dbService->select();
		$select->from('crmuser', '*')->
		joinInner('itemwatch', 'itemwatch.userid=crmuser.username', new Zend_Db_Expr('itemwatch.userid as itemwatchuser'));
		$select->where('itemid=?', $note->attachedtoid)->
		where('itemtype=?', $note->attachedtotype);

		$users = $this->dbService->fetchObjects('CrmUser', $select);

		if (!count($users)) {
			return;
		}

		$message = new TemplatedMessage('notification.php', array('model' => $note, 'toUrl' => $toUrl));

		$this->notifyUser('Note subscription: '.$note->title, $users, $message);
	}

	/**
	 * Gets an existing watch if it exists
	 *
	 * @param CrmUser $user
	 * @param int $id
	 * @param string $type
	 */
	public function getWatch($user, $id, $type=null)
	{
		if ($user == null) {
			throw new Exception("User cannot be null");
		}
   
		// if passed with 2 params, assume an object as $id
		if ($type==null) {
			$type = get_class($id);
			$id = $id->id;
		}
   
		$select = $this->dbService->select();
		$select->from('itemwatch', '*')->
		where('itemtype = ?', $type)->
		where('itemid = ?', $id)->
		where('userid= ?', $user->username);

		$select->order('created desc');
		return $this->dbService->getObject($select, 'ItemWatch');
	}

	/**
	 * Gets a list of all items a user is watching
	 *
	 * @param $user
	 * 			The user for whom to get subscribed items
	 * @return
	 * 			List of items
	 */
	public function getWatchedItems($user, $type=null)
	{
		$select = $this->dbService->select();
   
		$where = array(
		'userid=' => $user->username,
		);
   
		$select->from('itemwatch', '*');
		if ($type != null) {
			$where['itemtype'] = is_array($type) ? $type : array($type);
			// $select->where('itemtype = ?', $type);
		}

		$this->dbService->applyWhereToSelect($where, $select);

		$select->order('created desc');
		$watches = $this->dbService->fetchObjects('ItemWatch', $select);

		$items = new ArrayObject();
		foreach ($watches as $watch) {
			$item = $this->dbService->getById($watch->itemid, $watch->itemtype);
			if ($item) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Get all the current subscribers to an item
	 *
	 * Returned array is indexed by username => watch details
	 */
	public function getSubscribers($id, $type)
	{
		if ($type == null) {
			$type = get_class($id);
			$id = $id->id;
		}

		$select = $this->dbService->select();
		$select->from('itemwatch', '*')->
		where('itemtype = ?', $type)->
		where('itemid = ?', $id);

		$select->order('created desc');
		$items = $this->dbService->fetchObjects('ItemWatch', $select);
		$subscribers = array();
		foreach ($items as $item) {
			$subscribers[$item->userid] = $item;
		}

		return $subscribers;
	}

	/**
	 * Remove all the subscribers to a particular item
	 *
	 */
	public function deleteAllSubscribers($id, $type)
	{
		$this->dbService->delete('ItemWatch', array('itemid=?'=>$id, 'itemtype=?'=>$type));
	}

	/**
	 * Creates a watch between a user and object
	 *
	 * @param CrmUser $user
	 * @param int $id
	 * @param string $type
	 */
	public function createWatch($user, $id, $type=null)
	{
		// If the $id param is actually an object
		if ($type == null) {
			$type = get_class($id);
			$id = $id->id;
		}
		$existing = $this->getWatch($user, $id, $type);
		if ($existing) {
			// don't create another one
			return;
		}

		$params = array(
		'itemid' => $id,
		'itemtype' => $type,
		'userid' => $user->getUsername(),
		);

		$this->dbService->saveObject($params, 'ItemWatch');
	}

	/**
	 * Remove a watch
	 *
	 * @param CrmUser $user
	 * @param int $id
	 * @param string $type
	 */
	public function removeWatch($user, $id, $type)
	{
		// just delete direct, no need to get the
		// object first...
		$this->dbService->delete('itemwatch', "userid=".$this->dbService->quote($user->username)." AND itemid=$id AND itemtype='$type'");
	}


	/**
	 * Enter description here...
	 *
	 * @param unknown_type $template
	 * @param unknown_type $model
	 */
	public function generateEmail($template, $model)
	{
		$view = new CompositeView();
		$view->setScriptPath(APP_DIR.'/views/emails');
		$view->assign($model);

		return $view->render($template);
	}
}


class TemplatedMessage
{
	/**
	 * String pointing to a template in the views/emails folder
	 *
	 * @var string
	 */
	public $template;
	/**
	 * An array of key => value that is available in the template
	 *
	 * @var array
	 */
	public $model;

	public function __construct($template, $model)
	{
		$this->template = $template;
		$this->model = $model;
	}
}
?>