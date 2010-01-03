<?php

require_once('../lib/tonic.php');
require_once('../resources/filesystem.php');

/**
 * @package Tonic/Tests
 */
class FilesystemTester extends UnitTestCase {
    
    function testReadFile() {
        
        $testFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tonicFilesystemTest';
        file_put_contents($testFilename, 'test');
        
        $config = array(
            'uri' => '/filesystem/one/tonicFilesystemTest'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 200);
        $this->assertEqual($response->body, 'test');
        
        unlink($testFilename);
        
    }
    
    function testReadDefaultDocument() {
        
        $testFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'default.html';
        file_put_contents($testFilename, 'test');
        
        $config = array(
            'uri' => '/filesystem/one/'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 200);
        $this->assertEqual($response->body, 'test');
        
        unlink($testFilename);
        
    }
    
}


/* Test resource definitions */

/**
 * @package Tonic/Tests
 * @uri /filesystem/one
 */
class TestFileSystem extends FilesystemResource {
    
    function __construct() {
        
        $this->path = sys_get_temp_dir();
        $this->uriStub = '/filesystem/one/';
        
    }
    
}

?>
