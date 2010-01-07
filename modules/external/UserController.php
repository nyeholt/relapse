<?php

include_once 'controllers/UserController.php';

class External_UserController extends UserController 
{
    /**
	 * Edit a user object.
	 *
	 */
	public function editAction()
	{
	    $id = (int) $this->_getParam('id');
	    $userToEdit = za()->getUser();

	    // if the user's an admin, give them the list of contacts 
        // to bind for this user
        if (za()->getUser()->isPower()) {
            // get all the contacts
            $this->view->contacts = $this->clientService->getContacts();
        }

	    $this->view->model = $userToEdit;
	    $this->view->themes = $this->getThemes();
	    $this->renderView('user/edit.php');
	}
}
?>