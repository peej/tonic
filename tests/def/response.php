<?php

/* Test resource definitions */

/**
 * @namespace Tonic\Tests
 * @uri /responsetest
 */
class TestResponse extends Resource {
    
    function get($request) {
        
        $response = new Response($request);
        $response->body = 'test';
        return $response;
        
    }
    
}

