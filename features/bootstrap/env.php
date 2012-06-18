<?php

require_once __DIR__.'/../../lib/tonic.php';

/* Test resource definitions */

if (!class_exists('NewResource')) {
    
    /**
     * @namespace Tonic\Tests
     * @uri /requesttest/one
     * @uri /requesttest/three/.+/four 12
     */
    class NewResource extends Resource {
    
    }

}

if (!class_exists('ChildResource')) {

    /**
     * @namespace Tonic\Tests
     * @uri /requesttest/one/two
     */
    class ChildResource extends NewResource {
    
    }

}

if (!class_exists('TestResource')) {
    
    /**
     * @namespace Tonic\Tests
     * @uri /resourcetest/one
     */
    class TestResource extends Resource {
        
        function get($request) {
            
            $response = new Response($request);
            $response->body = 'test';
            return $response;
            
        }
        
    }

}

if (!class_exists('TestFileSystem')) {

    require_once 'examples/filesystem/filesystem.php';

    /**
     * @namespace Tonic\Tests
     * @uri /filesystemtest/one
     * @uri /filesystemtest/one/.*
     */
    class TestFileSystem extends FilesystemResource {
        
        function __construct($parameters) {
            
            parent::__construct($parameters);
            $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tonictest'.DIRECTORY_SEPARATOR;
            $this->uriStub = '/filesystemtest/one/';
            
        }
        
    }

}