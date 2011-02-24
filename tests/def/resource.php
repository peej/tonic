<?php

/* Test resource definitions */

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

/**
 * @namespace Tonic\Tests
 * @uri /resourcetest/badconstructor
 */
class TestBadResourceConstructor extends Resource {
    
    function __construct() {
        
        parent::__construct();
        
    }
    
}

