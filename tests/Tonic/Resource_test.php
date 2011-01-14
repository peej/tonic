<?php

namespace Tonic\Tests;

use Tonic as Tonic;
use UnitTestCase as UnitTestCase;

require_once ('../lib/Tonic/Resource.php');

class ResourceTester extends UnitTestCase {
    
    function testStandardResourceExec() {
        
        $config = array(
            'uri' => '/resourcetest'
        );
        
        $request = new Tonic\Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, '404');
        $this->assertEqual($response->body, 'Nothing was found for the resource "/resourcetest".');
        
    }
    
    function testFunctioningResourceExec() {
        
        $config = array(
            'uri' => '/resourcetest/one'
        );
        
        $request = new Tonic\Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
        $this->assertEqual($response->code, '200');
        $this->assertEqual($response->body, 'test');
        
    }

}


/* Test resource definitions */

/**
 * @uri /resourcetest/one
 */
class TestResource extends Tonic\Resource {
    
    function get($request) {
        
        $response = new Tonic\Response($request);
        $response->body = 'test';
        return $response;
        
    }
    
}
