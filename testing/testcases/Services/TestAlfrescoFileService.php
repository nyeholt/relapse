<?php

class TestAlfrescoFileService extends UnitTestCase 
{
    function testGetFolderByPath()
    {
        $fileService = za()->getService('FileService');
        if (!$fileService instanceof AlfrescoFileService) {
            return;
        }
        /* @var $fileService AlfrescoFileService */
        
        $node = $fileService->getNodeByPath('/Guest Home');
        
        $this->assertEqual($node->cm_name, 'Guest Home');
        
        
    }
    
    function testCreateFile()
    {
        $fileService = za()->getService('FileService');
        /* @var $fileService AlfrescoFileService */
        if (!$fileService instanceof AlfrescoFileService) {
            return;
        }
        $node = $fileService->getNodeByPath('/Guest Home');
        
        $this->assertEqual($node->cm_name, 'Guest Home');
        
        $file = null;
        try {
            $file = $fileService->createFile('sample_file.txt', '/Guest Home');
        } catch (FileExistsException $e) {
            return;
        }
        $this->assertNotNull($file->id);
        $this->assertNotNull($file->path);
    }
    
    function testCreateDirectory()
    {
        $fileService = za()->getService('FileService');
        /* @var $fileService AlfrescoFileService */
        $fileService->createDirectory('/My/Test/Folder Structure');
        $fileService->createDirectory('/My/Other/Folder Structure');
    }
    
    function testListDirectory()
    {
        $fileService = za()->getService('FileService');
        /* @var $fileService AlfrescoFileService */
        try {
            $file = $fileService->createFile('sample_file.txt', '/My');
            
            $fileService->setFile($file, __FILE__);
        } catch (FileExistsException $fee) {
            // don't do anything, we don't really care
        }
        
        $list = $fileService->listDirectory('/My');
        
        $this->assertEqual(3, count($list));
    }
}
?>