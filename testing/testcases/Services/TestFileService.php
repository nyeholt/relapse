<?php

class TestFileService extends UnitTestCase 
{
    function testCreateDirectory()
    {
        $fileService = za()->getService('FileService');
        /* @var $fileService FileService */
        
        $this->assertTrue($fileService->createDirectory('Company Home'));
        $this->assertTrue(is_dir(APP_DIR.'/testing/data/files/Company Home'));
        
        $this->assertTrue($fileService->removeDirectory('Company Home'));
    }
    
    function testCreateFile()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('file');
        
        $fileService = za()->getService('FileService');
        /* @var $fileService FileService */
        
        $file = $fileService->createFile('Company Home/client/new_file.txt');
        
        $this->assertEqual($file->filename, 'new_file.txt');
        $this->assertEqual($file->path, '/Company Home/client');
        
        $this->assertTrue(is_file(APP_DIR.'/testing/data/files/Company Home/client/new_file.txt'));
        
        $this->assertTrue($fileService->deleteFile($file));
    }
    
    function testListDirectory()
    {
        $dbService = za()->getService('DbService');
        /* @var $dbService DbService */
        $dbService->delete('file');
        
        $fileService = za()->getService('FileService');
        /* @var $fileService FileService */
        
        $file = $fileService->createFile('Company Home/client/new_file.txt');
        
        $this->assertEqual($file->filename, 'new_file.txt');
        $this->assertEqual($file->path, '/Company Home/client');
        
        $this->assertTrue(is_file(APP_DIR.'/testing/data/files/Company Home/client/new_file.txt'));
        
        $anotherFile = $fileService->createFile('Company Home/client/another_file.txt');
        
        $this->assertEqual($anotherFile->filename, 'another_file.txt');
        $this->assertEqual($anotherFile->path, '/Company Home/client');
        
        $this->assertTrue(is_file(APP_DIR.'/testing/data/files/Company Home/client/another_file.txt'));
        
        $fileService->createDirectory('/Company Home/client/another_Folder');
        
        $listing = $fileService->listDirectory('/Company Home/client');
        // okay, get the listing
        $this->assertEqual(3, count($listing));
    }
}
?>