<?php

require_once __DIR__.'/../../lib/tonic.php';

$world->config = array();

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

if (!class_exists('NewNoResource')) {

    /**
     * @namespace Tonic\Tests
     */
    class NewNoResource extends NoResource {
    
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
