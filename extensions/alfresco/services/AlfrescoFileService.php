<?php

include_once 'extensions/filemanager/model/File.php';
include_once 'extensions/filemanager/exceptions/FileExistsException.php';


class AlfrescoFileService implements Configurable
{
    private $alfrescoUrl;
    private $alfrescoUser;
    private $alfrescoPass;
    private $aspects; 
    
    /**
     * The alfresco session
     *
     * @var Session
     */
    private $alfresco;

    /**
     * The main store for files
     *
     * @var SpacesStore
     */
    private $spacesStore;

    public function configure($config)
    {

		require_once('Alfresco/Service/Repository.php');
		require_once('Alfresco/Service/Session.php');
		include_once 'Alfresco/Service/SpacesStore.php';
		include_once 'Alfresco/Service/ContentData.php';

        $this->alfrescoUrl = ifset($config, 'alfresco_url');

        if (!empty($this->alfrescoUrl)) {
            $this->alfrescoUser = ifset($config, 'alfresco_user');
            $this->alfrescoPass = ifset($config, 'alfresco_pass');
            $this->aspects = ifset($config, 'aspects_to_apply', array());
        }
    }
    
    protected function getConnection()
    {
        if ($this->alfresco == null) {
	        if (!empty($this->alfrescoUrl)) {
	            $repository = new Repository($this->alfrescoUrl);
	            try {
	                $ticket = $repository->authenticate($this->alfrescoUser, $this->alfrescoPass);
	            } catch (Exception $e) {
	                za()->log()->err("Failed authenticating to Alfresco");
	                return;
	            } 
	            $this->alfresco = $repository->createSession($ticket);
	            $this->spacesStore = new SpacesStore($this->alfresco);

	        }
        }
        
        return $this->alfresco;
    }

    /**
     * Creates a new file object
     *
     * @param string $filename
     * @param string $path
     * @return File
     */
    public function createFile($filename, $path=false)
    {
        if (!$this->getConnection()) {
            return null;
        }
        if ($path === false) {
            $path = $this->normalizePath(dirname($filename));
            $filename = basename($filename);
        } else {
            $path = $this->normalizePath($path);
        }

        $existing = $this->getNodeByPath($path.'/'.$filename);
        if ($existing) {
            throw new FileExistsException("File $path/$filename already exists");
        }

        // get the node for the given path
        $parent = $this->getNodeByPath($path);
        if (!$parent) {
            $this->log->debug("Creating parent path $path");
            $parent = $this->createDirectory($path);
        }

        $assocName = 'cm_'.mb_strtolower(preg_replace('/[^a-zA-Z.-_]/', '', $filename));
        $newFile = $parent->createChild("cm_content", "cm_contains", $assocName);
        $newFile->cm_name = $filename;

        foreach ($this->aspects as $aspect) {
            $this->log->debug("Adding aspect $aspect");
            $newFile->addAspect($aspect);
        }

        /* @var $newFile Node */
        $this->alfresco->save();

        $fileObject = $this->nodeToFile($newFile);

        return $fileObject;
    }

    /**
     * Get the node for a given path.
     *
     * @param string $path
     */
    public function getNodeByPath($path, $node = null)
    {
        if (!$this->getConnection()) {
            return null;
        }
        
        $bits = split('/', $path);

        if ($node == null) {
            $node = $this->spacesStore->getCompanyHome();
        }

        /* @var $node Node */
        foreach ($bits as $bit) {
            // Skip up until we have the parts after the company home
            if (empty($bit) || $bit == 'Company Home') {
                continue;
            }

            // Get the node for the current bit. If the current
            // "bit" doesn't exist, then
            $children = $node->getChildren();
            foreach ($children as $child) {
                $childNode = $child->getChild();
                if ($childNode->cm_name == $bit) {
                    $node = $childNode;

                    // Cheat and skip to the next 'bit' to scan
                    continue 2;
                }
            }

            $node = null;
            break;
        }

        return $node;
    }

    /**
     * Sets the content of a file object to that in
     * the given source file
     *
     * @param File $file
     * @param string $sourceFile
     */
    public function setFile(File $file, $sourceFile)
    {
        if (!$this->getConnection()) {
            return null;
        }
        ini_set('memory_limit', '64M');
        $node = $this->nodeFromFile($file);
        if (!$node) return;
        
        $content = new ContentData($node, NamespaceMap::getFullName("cm_content"));
        $mimeType = get_mime_content_type($sourceFile);
        $sourceContent = file_get_contents($sourceFile);

        $content->setContent($sourceContent);
        $content->setMimetype($mimeType);
        $node->cm_content = $content;
        $this->alfresco->save();
    }

    /**
     * Deletes a file object from the system
     * 
     * 
     *
     * @param File $file
     * @return boolean
     */
    public function deleteFile(File $file)
    {
        throw new Exception("Not implemented yet!");
    }

    /**
     * Gets a file object by its id
     *
     * @param string $id
     */
    public function getFile($id)
    {
        if (!$this->getConnection()) {
            return null;
        }
        $node = $this->alfresco->getNode($this->spacesStore, $id);

        $fileObject = $this->nodeToFile($node);

        return $fileObject;
    }

    /**
     * Gets a file object by its path
     *
     * @param string $id
     */
    public function getFileByPath($path)
    {
        $node = $this->getNodeByPath($this->normalizePath($path));
        if ($node == null) {
            return null;
        }
        $fileObject = $this->nodeToFile($node);

        return $fileObject;
    }

    /**
     * Save a file object
     *
     * @param File $file
     */
    public function saveFile(File $file)
    {
        if (!$this->getConnection()) {
            return null;
        }
        
        $node = $this->nodeFromFile($file);
        if (!$node) {
            return null;
        }
        $this->alfresco->save();
    }

    /**
     * Convert an alfresco node to a File object
     *
     * @param Node  $node
     */
    private function nodeToFile($node)
    {
        if (!$this->getConnection()) {
            return null;
        }
        
        if ($node == null) {
            throw new Exception("Cannot convert null node to File");
        }
        $fileObject = new File();
        $fileObject->id = $node->getId();
        $fileObject->path = $node->getPrimaryParent()->__toString();

        $fileObject->filename = $node->cm_name;
        $fileObject->title = $node->cm_title;
        $fileObject->description = $node->cm_description;
        $fileObject->created = $node->cm_created;
        $fileObject->updated = $node->cm_modified;
        $props = $node->getProperties();
        $fileObject->isprivate = ifset($props, '{simplecrm.model}isPrivate', "false") == "true" ? 1 : 0; 
        
        return $fileObject;
    }

    /**
     * Get a node from a file object.
     *
     * @param File $file
     * @return Node
     */
    private function nodeFromFile(File $file)
    {
        $node = $this->getNode($file->id);
        if (!$node) {
            throw new Exception("Node could not be found for file $file->id");
        }

        $node->cm_name = $file->filename;
        $node->cm_title = $file->title;
        $node->cm_description = $file->description;
        $props = $node->getProperties();
        $props['{simplecrm.model}isPrivate'] = $file->isprivate ? "true" : "false";
        $node->setProperties($props);

        return $node;
    } 

    /**
     * Set the content of a node
     */
    public function setFileContent(File $file, $content, $mimetype=null)
    {
        if (!$this->getConnection()) {
            return null;
        }
        $node = $this->nodeFromFile($file);
        $node->setContent('cm_content', $mimetype, null, $content);
        $this->alfresco->save();
    }

    /**
     * Our own version of the alfresco session getNode where it actually
     * gets the node instead of just querying a cache. 
     *
     * @param string $id
     */
    public function getNode($id)
    {
        if (!$this->getConnection()) {
            return null;
        }
        $node = $this->alfresco->getNode($this->spacesStore, $id);
        /* @var $node Node */
        if ($node) {
            return $node;
        }

        $nodes = $this->alfresco->query($this->spacesStore, '@sys:node-uuid:"'.$id.'"');
        return isset($nodes[0]) ? $nodes[0] : null;
    }
    
    /**
     * See if the given file is in a given path
     */
    public function isInDirectory(File $theFile, $path, $recursive = false)
    {
        $files = $this->listDirectory($path);
        $okay = false;
        foreach ($files as $file) {
            $inDirectory = false;
            if (is_string($file) && $recursive) {
                // loop it
                $inDirectory = $this->isInDirectory($theFile, $path.'/'.$file);
                if ($inDirectory) {
                    return true;
                }
            } else if ($file instanceof $file) {
	            if ($file->id == $theFile->id) {
	                return true;
	            }
            }
        }
        return false;
    }

    /**
     * Get all the items in a given path
     *
     * @param string $path
     * @return ArrayObject
     */
    public function listDirectory($path)
    {
    	// Get all the directories
        $list = new ArrayObject();
		try {
	        if (!$this->getConnection()) {
	            return array();
	        }
	
	        if (strpos($path, 'workspace://') === 0) {
	            $id = str_replace('workspace://SpacesStore/', '', $path);
	            $parent = $this->getNode($id);
	        } else {
	            $parent = $this->getNodeByPath($this->normalizePath($path));
	        }
	
	        if (!$parent) {
	            return $list;
	            // throw new Exception("Failed getting children for path $path");
	        }
	
	        // list everything using the node->getChildren()
	        $children = $parent->getChildren();
	        foreach ($children as $childAssoc) {
	            $child = $childAssoc->getChild();
	            /* @var $child Node */
	            if (strpos($child->getType(), 'folder')) {
	                $list->append($child->cm_name);
	            } else {
	                // content
	                $fileObject = $this->nodeToFile($child);
	                if (za()->getUser()->getRole() == User::ROLE_EXTERNAL && $fileObject->isprivate) {
	                    continue;
	                }
	                $list->append($fileObject);
	            }
	        }
		} catch (Exception $e) {
			$this->log->err("Failed to load files from $path: ".$e->getMessage());
		}

        return $list;
    }
    
    /**
     * Streams the content for a given file
     *
     * @param File $file
     */
    public function streamFile(File $file)
    {
        $node = $this->nodeFromFile($file);
        if (!$node) {
            echo "Failed retrieving file content";
        }
        
        $contentData = $node->cm_content;
        $filetype = $contentData->getMimetype();
        header("Content-type: $filetype");
        header("Content-Disposition: inline; filename=\"{$file->filename}\";");
        
        if ($contentData->getSize()) {
			header("Content-Length: ".$contentData->getSize());
		}
    
		readfile($contentData->getUrl());
        // header("Location: ".$contentData->getUrl());
        exit();
    }

    /**
     * Convert a path into a direct file path
     *
     * @param string $path
     */
    private function normalizePath($name)
    {
        $name = preg_replace('|^/Company Home|', '', $name);
        $name = str_replace('\\', '/', $name);
        $name = str_replace('../', '', $name);
        $name = str_replace('./', '', $name);

        if (strpos($name, '/') !== 0) {
            $name = '/'.$name;
        }
        $name = '/Company Home'.$name;

        
        return rtrim($name, '/');
    }

    /**
     * Create a directory. If a multiple depth, will recursively
     * call itself 
     *
     * @param string $name
     * @return Node the alfresco node representing the directory
     */
    public function createDirectory($name)
    {
        $name = $this->normalizePath($name);

        // okay, does the current path already exist?
        $existing = $this->getNodeByPath($name);
        if ($existing) {
            za()->log("Returning existing ".$existing->cm_name);
            return $existing;
        }

        $next_pathname = substr($name, 0, strrpos($name, '/'));
        if ($this->createDirectory($next_pathname)) {
            $existing = $this->getNodeByPath($name);
            if (!$existing) {
                $parent = $this->getNodeByPath($next_pathname);
                return $this->createFolder($parent, basename($name));
            } else {
                return $existing;
            }
        }
    }

    /**
     * Create a child folder given a parent node
     *
     * @param Node $parent
     * @param string $name
     */
    private function createFolder($parent, $name)
    {
        if (!$this->getConnection()) {
            return null;
        }
        $childassoc = preg_replace('/[^a-z0-9:.-]/', '', $name);
        $node = $parent->createChild('cm_folder', 'cm_contains', 'cm_'.$childassoc);

        $node->cm_name = $name;
        $this->alfresco->save();
        return $node;
    }

    /**
     * Delete a file from the filesystem
     *
     * @param string $name the filename with directory prefix
     * @return boolean
     */
    private function removeFile($name)
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * Delete a directory
     *
     * @param string $name
     */
    public function removeDirectory($name)
    {
        throw new Exception("Not implemented yet");
    }
}

?>
