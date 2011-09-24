<?php

require_once('../lib/tonic.php');
require_once('../examples/filesystem/filesystem.php');
require_once('../examples/filesystem/filesystemcollection.php');

/**
 * @namespace Tonic\Tests
 */
class FilesystemCollectionTester extends UnitTestCase {
    
    var $testPath;
    
    function __construct() {
        $this->testPath = sys_get_temp_dir();
    }
    
    function setUp() {
        @mkdir($this->testPath.DIRECTORY_SEPARATOR.'collection');
        file_put_contents($this->testPath.DIRECTORY_SEPARATOR.'collectionIndex', '<ul>{{resources}}</ul>');
        file_put_contents($this->testPath.DIRECTORY_SEPARATOR.'collection'.DIRECTORY_SEPARATOR.'1', 'one');
        file_put_contents($this->testPath.DIRECTORY_SEPARATOR.'collection'.DIRECTORY_SEPARATOR.'2', 'two');
    }
    
    function tearDown() {
        unlink($this->testPath.DIRECTORY_SEPARATOR.'collectionIndex');
        unlink($this->testPath.DIRECTORY_SEPARATOR.'collection'.DIRECTORY_SEPARATOR.'1');
        unlink($this->testPath.DIRECTORY_SEPARATOR.'collection'.DIRECTORY_SEPARATOR.'2');
        @unlink($this->testPath.DIRECTORY_SEPARATOR.'collection'.DIRECTORY_SEPARATOR.'3');
        rmdir($this->testPath.DIRECTORY_SEPARATOR.'collection');
    }
    
    function testCollection() {
        
        $config = array(
            'uri' => '/filesystemcollection/collectionIndex'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 200);
        $this->assertEqual($response->body, '<ul><li><a href="/filesystemcollection/collection/1">1</a></li><li><a href="/filesystemcollection/collection/2">2</a></li></ul>');
        
    }
    
    function testReadCollectionItem() {
        
        $config = array(
            'uri' => '/filesystemcollection/collection/1'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 200);
        $this->assertEqual($response->body, 'one');
        
    }
    
    function testCreateCollectionItem() {
        
        $config = array(
            'uri' => '/filesystemcollection/collectionIndex',
            'method' => 'POST'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 411);
        
        $config = array(
            'uri' => '/filesystemcollection/collectionIndex',
            'method' => 'POST',
            'data' => 'three'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 201);
        $this->assertEqual($response->headers['Location'], '/filesystemcollection/collection/3');
        
        $config = array(
            'uri' => '/filesystemcollection/collectionIndex'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, 200);
        $this->assertEqual($response->body, '<ul><li><a href="/filesystemcollection/collection/1">1</a></li><li><a href="/filesystemcollection/collection/2">2</a></li><li><a href="/filesystemcollection/collection/3">3</a></li></ul>');
        
    }
    
}


/* Test resource definitions */

/**
 * @namespace Tonic\Tests
 * @uri /filesystemcollection/collection/.*
 */
class TestFileSystemCollectionItem extends FilesystemResource {
    
    function __construct() {
        
        $this->path = sys_get_temp_dir();
        $this->uriStub = '/filesystemcollection';
        
    }
    
}

/**
 * @namespace Tonic\Tests
 * @uri /filesystemcollection/collectionIndex
 */
class TestFileSystemCollection extends FilesystemCollection {
    
    function __construct() {
        
        $this->path = sys_get_temp_dir();
        $this->uriStub = '/filesystemcollection';
        $this->collection = sys_get_temp_dir().'/collection';
        
    }
    
}

