<?php
class FileService implements Configurable
{
    /**
     * Where we store the files
     *
     * @var string
     */
    private $fileRoot;

    /**
     * The database service
     *
     * @var DbService
     */
    public $dbService;

    public function configure($config)
    {
        $this->fileRoot = APP_DIR.ifset($config, 'root', '/data/files');
        if (!is_dir($this->fileRoot)) {
            // failure!
            if (!mkdirr($this->fileRoot, 0775)) {
                throw new Exception("Could not find file root at $this->fileRoot");
            }
        }
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
        if ($path === false) {
            $path = dirname($filename);
            $filename = basename($filename);
        }
        
        $params['filename'] = $filename;
        if (!$this->createDirectory($path)) {
            return null;
        }
        $params['path'] = $this->normalizePath($path);
        $params['owner'] = za()->getUser()->getUsername();

        $existing = $this->getFileByPath($params['path'].'/'.$filename);
        if ($existing) {
            throw new Exception("File $filename already exists in '$path'");
        }
        
        $filename = $this->fileRoot.$params['path'].'/'.$filename;
        
        if (!is_file($filename)) {
            $fp = fopen ($filename, "w");
            if ($fp) {
                fclose($fp);
            } else {
                throw new Exception("Could not find file handle for $filename");
            }
            if (!is_file($filename)) {
                throw new Exception("Failed to create file $filename");
            }
        }

        return $this->dbService->saveObject($params, 'File');
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
        $filename = $this->fileRoot.$file->path.'/'.$file->filename;
        $fp = null;
        $fp = fopen ($filename, "w");
        if (!$fp) {
            throw new Exception("Cannot open $filename");
        }
        
        $rp = fopen($sourceFile, "r");
        if (!$rp) { 
            throw new Exception("Cannot read $sourceFile");
        }

        fwrite($fp, fread($rp, filesize($sourceFile)));

        fclose($fp);
        fclose($rp);
    }
    
    /**
     * Set the content of a file
     *
     * @param File $file
     * @param mixed $content
     */
    public function setFileContent(File $file, $content, $mimetype=null)
    {
        $filename = $this->fileRoot.$file->path.'/'.$file->filename;
        $fp = null;
        $fp = fopen ($filename, "w");
        if (!$fp) {
            throw new Exception("Cannot open $filename");
        }
        fwrite($fp, $content);
        fclose($fp);
    }
    
    /**
     * Deletes a file object from the system
     *
     * @param File $file
     * @return boolean
     */
    public function deleteFile(File $file)
    {
        if (!$this->removeFile($file->path.DIRECTORY_SEPARATOR.$file->filename)) {
            $this->log->debug("Failed to delete file ".$file->filename);
            return false;
        } 

        return $this->dbService->delete($file);
    }
    
    /**
     * Gets a file object by its id
     *
     * @param string $id
     */
    public function getFile($id)
    {
        return $this->dbService->getById((int) $id, 'File');
    }
    
    /**
     * Gets a file object by its id
     *
     * @param string $id
     */
    public function getFileByPath($path)
    {
        $dir = $this->normalizePath(dirname($path));
        $filename = basename($path);
        za()->log("Searching for path $path : $dir and $filename");
        return $this->dbService->getByField(array('path'=>$dir, 'filename'=>$filename), 'File');
    }
    
    /**
     * Save a file object
     *
     * @param File $file
     */
    public function saveFile(File $file)
    {
        $file->path = $this->normalizePath($file->path);
        $this->dbService->saveObject($file);
    }
    
    /**
     * See if the given file is in a given path
     */
    public function isInDirectory(File $file, $path)
    {
        return true;
    }
    
    /**
     * Get all the items in a given path. If it is a directory,
     * a string value is entered. If it is a file, a File object
     * is entered.
     * 
     * ArrayObject(
     * 	0 => 'string',
     *  1 => File{}
     *  2 => 'etc'
     * )
     *
     * @param string $path
     * @return ArrayObject
     */
    public function listDirectory($path)
    {
        // Get all the directories
        $list = new ArrayObject();
        $listFrom = $this->fileRoot.$this->normalizePath($path);
        
        try {
            $dir = new DirectoryIterator($listFrom);
        } catch (Exception $e) {
            return $list;
        }
        
        foreach ($dir as $entry) {
            /* @var $entry DirectoryIterator */
            if ($entry->isDot()) continue;
            
            if (is_dir($this->fileRoot.$this->normalizePath($path).DIRECTORY_SEPARATOR.$entry->getFilename())) {
                $list->append($entry->getFilename());
            }
        }

        $conditions = array('path' => $this->normalizePath($path));
        if (za()->getUser()->getRole() == User::ROLE_EXTERNAL) {
            $conditions = array('isprivate' => 0);
        }

        $results = $this->dbService->getManyByFields($conditions, 'File');
        
        foreach ($results as $file) {
            $list->append($file);
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
        $filename = $this->getFullFilename($file);
        
        $filetype = get_mime_content_type($filename);
        // just raw stream the file
        header("Content-type: $filetype");
		header("Content-Disposition: inline; filename=\"{$file->filename}\";");
		$size = filesize($filename);
		if ($size) {
			header("Content-Length: ".$size);
		}
		
		readfile($filename);
		exit();
    }
    
    protected function getFullFilename(File $file)
    {
        $filename = $this->fileRoot.$file->path.'/'.$file->filename;
        return $filename;
    }
    
    /**
     * Convert a path into a direct file path
     *
     * @param string $path
     */
    private function normalizePath($name)
    {
        $name = str_replace('\\', '/', $name);
        $name = str_replace('../', '', $name);
        $name = str_replace('./', '', $name);

        if (strpos($name, '/') !== 0) {
            $name = '/'.$name;
        }
        return $name;
    }

    /**
     * Create a directory. If a multiple depth, will recursively
     * call itself 
     *
     * @param string $name
     * @return boolean
     */
    public function createDirectory($name)
    {
        $this->log->debug("Creating folder $name");
        return mkdirr($this->fileRoot.$this->normalizePath($name), 0775);
    }
    
    /**
     * Delete a file from the filesystem
     *
     * @param string $name the filename with directory prefix
     * @return boolean
     */
    private function removeFile($name)
    {
        return unlink($this->fileRoot.$this->normalizePath($name));
    }
    
    /**
     * Delete a directory
     *
     * @param string $name
     */
    public function removeDirectory($name)
    {
        return full_rmdir($this->fileRoot.$this->normalizePath($name));
    }
}

/**
 * Create a directory structure recursively
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/repos/v/function.mkdirr.php
 * @param       string   $pathname    The directory structure to create
 * @return      bool     Returns TRUE on success, FALSE on failure
 */

function mkdirr($pathname, $mode = null)
{
    // Check if directory already exists
    if (is_dir($pathname) || empty($pathname)) {
        return true;
    }

    // Ensure a file does not already exist with the same name
    $pathname = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pathname);
    if (is_file($pathname)) {
        trigger_error('mkdirr() File exists', E_USER_WARNING);
        return false;
    }

    // Crawl up the directory tree
    $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
    if (mkdirr($next_pathname, $mode)) {
        if (!file_exists($pathname)) {
            return mkdir($pathname, $mode);
        }
    }

    return false;
}

function full_rmdir($dirname)
{
    if ($dirHandle = opendir($dirname)){
        $old_cwd = getcwd();
        chdir($dirname);

        while ($file = readdir($dirHandle)){
            if ($file == '.' || $file == '..') continue;

            if (is_dir($file)){
                if (!full_rmdir($file)) return false;
            }else{
                if (!unlink($file)) return false;
            }
        }

        closedir($dirHandle);
        chdir($old_cwd);
        if (!rmdir($dirname)) return false;

        return true;
    }else{
        return false;
    }
}
?>
