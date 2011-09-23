<?php

require_once('../lib/tonic.php');
require_once('../examples/filesystem/filesystem.php');

/**
 * @namespace Tonic\Tests
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
    
    function testReadNoResource() {
        
        $config = array(
            'uri' => '/filesystem/one/tonicFilesystemTestDoesntExist'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 404);
        
    }
    
    function testCreateResource() {
        
        $testFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tonicFilesystemTest';
        
        $config = array(
            'uri' => '/filesystem/one/tonicFilesystemTest',
            'method' => 'PUT'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 411);
        
        $config['data'] = 'test';
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 204);
        $this->assertTrue(file_exists($testFilename));
        $this->assertEqual(file_get_contents($testFilename), 'test');
        
        unlink($testFilename);
        
    }
    
    function testDeleteResource() {
        
        $testFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tonicFilesystemTest';
        file_put_contents($testFilename, 'test');
        
        $config = array(
            'uri' => '/filesystem/one/tonicFilesystemTest',
            'method' => 'DELETE'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 204);
        $this->assertFalse(file_exists($testFilename));
        
    }
    
    function testDeleteNoResource() {
        
        $config = array(
            'uri' => '/filesystem/one/tonicFilesystemTestDoesntExist',
            'method' => 'DELETE'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 404);
        
    }
    
}


/* Test resource definitions */

/**
 * @namespace Tonic\Tests
 * @uri /filesystem/one
 * @uri /filesystem/one/.*
 */
class TestFileSystem extends FilesystemResource {
    
    function __construct($parameters) {
        
        parent::__construct($parameters);
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR;
        $this->uriStub = '/filesystem/one/';
        
    }
    
}

