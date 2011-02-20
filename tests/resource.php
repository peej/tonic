<?php

require_once('../lib/tonic.php');
require_once('def/resource.php');

/**
 * @namespace Tonic\Tests
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

