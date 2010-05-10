<?php

class UserController extends NovemberController 
{
	/**
	 * The authentication service, IOC'd in
	 *
	 * @var DbAuthService
	 */
	public $authService;
	
	/**
	 * The user service
	 *
	 * @var UserService
	 */
	public $userService;
	
	/**
	 * @var ClientService
	 */
	public $clientService;
	
	public function indexAction()
	{
	    $this->editAction();
	}
	
    public function listAction()
	{
	    $this->editAction();
	}
	
	public function loginAction()
	{
		// If we're not a post, then just show the
		// login form
		if ($this->_getParam('username')) {
			
			if ($this->authService->authenticate($this->_getParam('username'), $this->_getParam('password'))) {
				// redirect to the return url if given
				$this->returnOrHome();
				return;
			} else {
				$this->view->addError('login', 'User not found');
			}
		} 

		$this->renderView('user/login.php');
	}
	
	/**
	 * Resets a user's password
	 */
	public function passwordAction()
	{
		if ($this->_getParam('email')) { // $post->getAlpha('email')) {
			$email = $this->_getParam('email'); // $post->testEmail('email');
			$emailValidator = new Zend_Validate_EmailAddress();
			
			if ($emailValidator->isValid($this->_getParam('email')) === false) {
				$this->view->addError('email', 'Invalid email address.');
			} else {
				if ($this->authService->resetPassword($email)) {
					$this->view->flash("Your password has been sent");
					$this->redirect('user', 'login');
					
				} else {
					$this->view->addError('email', 'Could not send password.');
				}
			}
		}

		$this->getResponse()->appendBody($this->view->render('user/password.php'));
	}
	
	/**
	 * Log the user out.
	 *
	 */
	public function logoutAction()
	{
		$this->authService->endSession();
		$this->redirect('index', 'index');
	}

	/**
	 * Display the register form
	 *
	 
	public function registerAction()
	{
		
		if ($this->_getParam('email')) {
			// alright then, lets create the user
			if ($this->_getParam('password') != $this->_getParam('confirm')) {
			    $this->view->addError('unmatched_password', 'Passwords do not match');
			} else {
    			try {
    				$this->userService->createUser($this->_getAllParams());
    				$this->returnOrHome();
    			} catch (ExistingUserException $eu) {
    				$this->view->addError('create_user', "Email ".$this->_getParam('email').' already exists');
    			} catch (InvalidModelException $im) {
    			    $this->view->addErrors($im->getMessages());
    			} catch (Exception $e) {
    				$this->view->addError('unknown', $e->getMessage());
    				error_log($e->getTraceAsString());
    			}
			}
		}
		$this->getResponse()->appendBody($this->view->render('user/signup.php'));
	}*/

	/**
	 * Edit a user object.
	 *
	 */
	public function editAction()
	{
	    $id = (int) $this->_getParam('id');
	    
	    $userToEdit = za()->getUser();
	    // If an ID is passed, we need to have a higher role than that user
        // to be able to edit them an admin to be
	    // able to edit this user
	    if ($id > 0) {
	        $selectedUser = $this->userService->getUser($id);
	        // now, if the selectedUser has a role less than mine, we can 
            // edit them
            if ($selectedUser->getRoleValue() < za()->getUser()->getRoleValue() || za()->getUser()->isPower()) {
	            $userToEdit = $selectedUser;
	        }
	    }

	    // if the user's an admin, give them the list of contacts 
        // to bind for this user
        if (za()->getUser()->hasRole(User::ROLE_USER)) {
            // get all the contacts
            $this->view->contacts = $this->clientService->getContacts();
        }
	    
	    $this->view->leave = $this->userService->getLeaveForUser($userToEdit);
	    $this->view->accruedLeave = $this->userService->calculateLeave($userToEdit);
	    $this->view->leaveApplications = $this->userService->getLeaveApplicationsForUser($userToEdit);
	    $this->view->model = $userToEdit;
	    
	    $this->view->themes = $this->getThemes();
	    
	    $this->renderView('user/edit.php');
	}
	
	protected function getThemes()
	{
		// $themes = array('Default');
		$themes = array('Relapse', 'Paned');
		return $themes;
		
		if (is_dir(BASE_DIR.'/themes')) {
			$themeDir = new DirectoryIterator(BASE_DIR.'/themes');
			foreach ($themeDir as $theme) {
				if (strpos($theme, '.') === 0) {
					continue;
				}
				$themes[] = ucfirst($theme);
			}
		}
		return $themes;
	}

	public function saveAction()
	{
	    $id = (int) $this->_getParam('id');
	    
	    $userToEdit = za()->getUser();
	    // If an ID is passed, we need to be an admin to be
	    // able to edit this user
	    if ($id > 0) {
	        $selectedUser = $this->userService->getUser($id);
	        // now, if the selectedUser has a role less than mine, we can 
            // edit them
            if ($selectedUser->getRoleValue() < za()->getUser()->getRoleValue() || za()->getUser()->isPower()) {
	            $userToEdit = $selectedUser;
	        }
	    }
	    // we're saving? 
	    // bind the user item
        if ($this->_getParam('password') != $this->_getParam('confirm')) {
		    $this->view->addError('unmatched_password', 'Passwords do not match');
		} else {
   	        try {
   	           $params = $this->_getAllParams();
   	           // If no new pass was set, just clear it from the update
   	           if ($params['password'] == '') {
   	               unset($params['password']);
   	           }
   	           $this->userService->updateUser($userToEdit, $params);
   	           $this->view->flash("Profile successfully updated");
   	        } catch (InvalidModelException $im) {
   	            $this->view->flash($im->getMessages());
   	            $this->redirect('user', 'edit', array('id'=>$userToEdit->id));
   	            return;
   	        } catch (ExistingUserException $eu) {
   	            $this->view->flash("User already exists");
   	            $this->redirect('user', 'edit', array('id'=>$userToEdit->id));
   	            return;
   	        }
		}

	    $this->redirect('user', 'edit', array('id'=>$userToEdit->id));
	}

	/**
	 * View a user and their session booking details. 
	 *
	 */
	public function viewAction()
	{
	    $id = (int) $this->_getParam('id');

	    $user = za()->getUser();
	    if (!$id) {
            $viewUser = $user;
	    } else {
	        $viewUser = $this->userService->getUser($id);
	    }

	    // If the user isn't the same as the current user, 
	    // we need to be an admin to view it
	    if (($viewUser->id != $user->id) && $user->getRole() != 'Admin') {
	        $this->view->flash("You must be logged in as an administrator to view that");
	        $this->redirect('user', 'login');
	        return;
	    }

	    // Okay, load up the user view page then!
	    $this->view->user = $viewUser;
	    
	    $this->renderView('user/view.php');
	}
	
	
}
?>