<?php

class NoteController extends BaseController 
{
    /**
     * @var NotificationService
     */
    public $notificationService;
    
    /**
     * @var UserService
     */
    public $userService;
    
    public function addAction()
    {
        $this->_setParam('attachedtotype', ucfirst($this->_getParam('attachedtotype')));
        $this->_setParam('id', '');
        $this->saveAction();
    }
    
    /**
     * Go through and notify users on saving
     *
     */
    public function onModelSaved($model)
    {
        // 
        $this->notificationService->sendWatchNotifications($model);
        // if there's a callign url, send back to there
        $caller = $this->getCallingUrl();
        if (mb_strlen($caller)) {
            $this->_redirect($caller);
        } else {
            $this->redirect('note', 'viewthread', array('totype'=>$model->attachedtotype, 'toid'=>$model->attachedtoid));
        }
    }
    
    /**
     * Update all the watchers for a given item
     */
    public function setwatchersAction()
    {
		$itemType = ucfirst($this->_getParam('attachedtotype'));
		$itemId = $this->_getParam('attachedtoid');
		if ($itemType==null || $itemId==null) {
			$this->flash("Could not update subscribers, null request passed");
			$this->redirect('error');
			return;
		}
		
		// get all watchers and then remove	
		$subscribers = $this->notificationService->deleteAllSubscribers($itemId, $itemType);
		foreach ($this->_getParam('watchusers') as $username) {
			$user = $this->userService->getUserByField('username', $username);
			$this->notificationService->createWatch($user, $itemId, $itemType);
		}
		
    	$caller = $this->getCallingUrl();
        if (mb_strlen($caller)) {
            $this->_redirect($caller);
        }
    }
    
    /**
     * Called after an object is deleted
     *
     * @param the deleted object $model
     */
    protected function onModelDeleted($model)
    {
        $this->flash('Deleted '.get_class($model).' #'.$model->id);
        
        // $this->redirect('note', 'viewthread', array('toid'=>$model->attachedtoid, 'totype'=>$model->attachedtotype));
        $this->_redirect($this->getCallingUrl());
    }
    
    /**
     * Get the list of notes for a given item of a given type
     *
     */
    public function viewAction()
    {
        $type = ucfirst($this->_getParam('type'));
        $id = $this->_getParam('id');
        
        $this->view->notes = $this->notificationService->getNotesFor($type, $id);
        $this->view->itemtype = $type;
        $this->view->itemid = $id;

        $this->view->existing = $this->notificationService->getWatch(za()->getUser(), $id, $type);
        
        $this->renderRawView('note/ajax-view.php');
    }
    
    /**
     * Add a watch to a given item of a given type
     *
     */
    public function addwatchAction()
    {
        $type = ucfirst($this->_getParam('type'));
        $id = $this->_getParam('id');
        $user = $this->_getParam('userid');
        if (!$user) {
            $user = za()->getUser();
        }

        $this->notificationService->createWatch($user, $id, $type);        
    }

    /**
     * Add a watch to a given item of a given type
     *
     */
    public function deletewatchAction()
    {
        $type = ucfirst($this->_getParam('type'));
        $id = $this->_getParam('id');
        $user = $this->_getParam('userid');
        if (!$user) {
            $user = za()->getUser();
        }
        
        $this->notificationService->removeWatch($user, $id, $type);
    }

    /**
     * Get the latest notes for the current user
     *
     */
    public function latestnotesAction()
    {
        $from = za()->getUser()->getLastLogin();
        $this->view->notes = $this->notificationService->getNoteThreads(array('created > '=> date('Y-m-d H:i:s', strtotime($from) - (30*86400))), "created desc", 1, 20);
        $this->renderRawView('note/list.php');
    }
    
    /**
     * view a note thread
     *
     */
    public function viewthreadAction()
    {
        $id = $this->_getParam('toid');
        $type = $this->_getParam('totype');
        
        if (!$id || !$type) {
            return;
        }
        
        $this->view->notes = $this->notificationService->getNotesFor($type, $id, 'created asc');
        if (!count($this->view->notes)) {
            $this->flash("No notes found in thread");
            $this->redirect('index');
            return;
        }
        $this->view->itemtype = $type;
        $this->view->itemid = $id;

        $this->view->existing = $this->notificationService->getWatch(za()->getUser(), $id, $type);

        
        $this->renderView('note/thread-view.php');
    }
    
    public function loadsourceAction()
    {
    	$note = $this->byId();
    	
    	echo $note->note;
    }
    
    public function ajaxupdateAction()
    {
    	$note = $this->byId();
    	if ($note) {
    		$note->note = $this->_getParam('value');
    	}
    	$this->saveObject($note, '');
    	echo $this->view->bbCode($note->note);
    }
}
?>