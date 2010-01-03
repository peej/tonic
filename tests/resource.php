<?php

require_once('../lib/tonic.php');

/**
 * @package Tonic/Tests
 */
class ResourceTester extends UnitTestCase {
    
    function testStandardResourceExec() {
        
        $config = array(
            'uri' => '/resourcetest'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, '404');
        $this->assertEqual($response->body, 'Nothing was found for the resource "/resourcetest".');
        
    }
    
    function testFunctioningResourceExec() {
        
        $config = array(
            'uri' => '/resourcetest/one'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, '200');
        $this->assertEqual($response->body, 'test');
        
    }

}


/* Test resource definitions */

/**
 * @package Tonic/Tests
 * @uri /resourcetest/one
 */
class TestResource extends Resource {
    
    function get($request) {
        
        $response = new Response($request);
        $response->body = 'test';
        return $response;
        
    }
    
}

?>
