<?php

include_once APP_DIR.'/extensions/filemanager/controllers/FileController.php';
include_once APP_DIR.'/extensions/filemanager/model/File.php';

class External_FileController extends FileController 
{
    
    /**
     * @var ClientService
     */
    public $clientService;

    /**
     * Before allowing someone to do stuff, check to see
     * whether they have access to the file they've requested
     * 
     */
    public function preDispatch()
    {
        if (za()->getUser()->getRole() == User::ROLE_EXTERNAL) {
	        // make sure the id is valid
	        $id = $this->_getParam('id');
	        $client = $this->clientService->getUserClient(za()->getUser());
	        $project = $this->byId($this->_getParam('projectid'), 'Project');
	        if ($client == null || $project == null) {
	            $this->log->warn("User ".za()->getUser()->getUsername()." tried viewing without valid client or project");
	            $this->requireLogin();
	            return;
	        }

	        if ($id) {
	            // see whether the list of files for the current user's
	            // company is valid
	            /*$path = 'Clients/'.$client->title.'/Projects/'.$project->title;
	            
	            $okay = $this->fileService->isInDirectory($this->fileService->getFile($id), $path, true);

	            if (!$okay) {
	                $this->requireLogin();
	            }*/
	        }
        }
    }

    public function indexAction()
    {
        $this->requireLogin();
    }

    /**
     * When creating a new client, show this action
     */
    public function editAction($model=null)
    {
        // figure out the model type based on the
        // name of this controller. 
        $modelType = 'File';

        if ($model == null) {
            if ($this->_getParam('id')) {
                // get the file from the file service
                $this->view->model = $this->fileService->getFile($this->_getParam('id'));
            } else {
                $this->view->model = new $modelType();
            }
        } else {
            $this->view->model = $model;
        }

        $this->view->projectid = $this->_getParam('projectid');
        $this->view->picker = $this->_getParam('picker');
        $this->view->parent = $this->_getParam('parent');
        $this->view->returnUrl = $this->_getParam('returnurl');

        $this->renderView('filemanager/edit.php');
    }
    
    /**
     * View a file
     *
     */
    public function viewAction()
    {
        $file = $this->fileService->getFile($this->_getParam('id'));
        $this->fileService->streamFile($file);
    }
    
    /**
     * View the thumbnail for a given image
     */
    public function viewthumbnailAction()
    {
        $file = $this->fileService->getFile($this->_getParam('id'));
        $this->fileService->streamTumbnailFile($file);
    }
    
    /**
     * Add a new file.
     *
     */
    public function uploadAction()
    {
        if (!$this->_getParam('updated')) {
            $this->_setParam('updated', date('Y-m-d H:i:s'));
        }

        $model = null;
        $parent = '';
        
        try {
            $params = $this->filterParams($this->_getAllParams());
            $doUpload = false;
            if (isset($_FILES['file']) && strlen(ifset($_FILES['file'],'tmp_name', ''))) {
                $doUpload = true;
            }
            
            $filename = ifset($this->_getAllParams(), 'filename');
            if (!strlen($filename) && $doUpload) {
                $filename = $_FILES['file']['name'];
            }
            
            $parent = trim(base64_decode($this->_getParam('parent')), " /");
            
            $id = $this->_getParam('id');
            if ($id) {
                // updating
                $file = $this->fileService->getFile($id);
            } else {
                if ($doUpload == false) {
                    // if we're not doing an upload, but the file doesn't 
                    // exist yet, lets bail
                    throw new Exception("You must upload a file");
                }
                $file = $this->fileService->createFile($filename, $parent);
            }

            if ($doUpload) {
                $this->fileService->setFile($file, $_FILES['file']['tmp_name']);
            }
            
            $params = $this->_getAllParams();
            $params['filename'] = $filename;
            if (!empty($parent)) {
                $params['path'] = $parent;
            }
            
            $file->bind($params);
            $this->fileService->saveFile($file);
        } catch (InvalidModelException $ime) {
            $this->flash("Invalid model: ".$ime->getMessages());
            $model = new File();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        } catch (Exception $e) {
            $this->flash(get_class($e).': '.$e->getMessage());
            error_log(get_class($e).': '.$e->getMessage());
            error_log($e->getTraceAsString());
            $model = new File();
            $model->bind($this->_getAllParams());
            $this->editAction($model);
            return;
        }
        
        $picker = $this->_getParam('picker');
        $returnUrl = $this->_getParam('returnurl');
        if (!empty($returnUrl)) {
            $this->redirect(base64_decode($this->_getParam('returnurl')), '', array('picker'=>$picker));
        } else {

            $this->redirect('file', 'index', array('picker'=>$picker, 'parent'=>base64_encode($parent)));
        }
    }
    
    /**
     * Delete a file
     *
     * 
     */
    public function deleteAction()
    {
        $id = $this->_getParam('id');
        $file = $this->fileService->getFile($id);
        if ($file) {
            if ($this->fileService->deleteFile($file)) {
            $this->flash("Deleted ".$file->getTitle());                
            } else {
                $this->flash("Failed deleting ".$file->getTitle());
            }
        }

        $picker = $this->_getParam('picker');
        $returnUrl = $this->_getParam('returnurl');
        if (!empty($returnUrl)) {
            $this->redirect(base64_decode($this->_getParam('returnurl')), '', array('picker'=>$picker));
        } else {
            $this->redirect('file', 'index', array('picker'=>$picker, 'parent'=>base64_encode($parent)));
        }
    }
}
?>