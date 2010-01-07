<?php

include_once APP_DIR.'/extensions/filemanager/model/File.php';
include_once APP_DIR.'/extensions/filemanager/controllers/FileController.php';

class Expenses_FileController extends FileController 
{
    /**
     * @var ImageFileService
     */
    public $fileService;
    
    /**
     * @var ClientService
     */
    public $clientService;
    
	public function indexAction()
    {
    	$lm = $this->clientService->getClient(za()->getConfig('owning_company'));

    	$path = "Clients/".$lm->title."/Accounts";

    	if ($this->_getParam('folder')) {
    		$basePath = base64_decode($this->_getParam('folder'));
    		
    		if ($basePath != $path && $basePath != $path."/"  && strpos($basePath, $path)===0) {
    			// we've got a 'starts with' situation, so lets give the view a parent path to deal with
				$this->view->parentPath = dirname($basePath);
    		}
    		$path = $basePath;
    	}

        // The target of a picker action. If set, we need to show the picker
        $this->view->picker = $this->_getParam('picker');

        // Get all top level files
        $files = $this->fileService->listDirectory($path);
        $this->view->files = $files;
        
        if ($path == '/') {
            $this->view->base = '';
        } else {
            $this->view->base = trim($path, '/').'/';
        }
        
        $this->renderView('filemanager/list.php');
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
    public function viewThumbnailAction()
    {
        $file = $this->fileService->getFile($this->_getParam('id'));
        $this->fileService->streamTumbnailFile($file);
    }
    
}
?>