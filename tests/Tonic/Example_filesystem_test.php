<?php

namespace Tonic\Tests;

use Tonic as Tonic;
use UnitTestCase as UnitTestCase;

require_once('../lib/Tonic/Request.php');
require_once('../lib/Tonic/Response.php');
require_once('../lib/Tonic/Resource.php');
require_once('../examples/filesystem/filesystem.php');

class FilesystemTester extends UnitTestCase {
    
    function testReadFile() {
        
        $testFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tonicFilesystemTest';
        file_put_contents($testFilename, 'test');
        
        $config = array(
            'uri' => '/filesystem/one/tonicFilesystemTest'
        );
        
        $request = new Tonic\Request($config);
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
        
        $request = new Tonic\Request($config);
        
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
        
        $request = new Tonic\Request($config);
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
        
        $request = new Tonic\Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 411);
        
        $config['data'] = 'test';
        
        $request = new Tonic\Request($config);
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
        
        $request = new Tonic\Request($config);
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
        
        $request = new Tonic\Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 404);
        
    }
    
}


/* Test resource definitions */

/**
 * @uri /filesystem/one
 * @uri /filesystem/one/.*
 */
class TestFileSystem extends Tonic\Examples\Filesystem\FilesystemResource {
    
    function __construct() {
        
        $this->path = sys_get_temp_dir();
        $this->uriStub = '/filesystem/one/';
        
    }
    
}

?>
