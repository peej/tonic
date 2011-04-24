<?php

require_once('def/resource.php');

/**
 * @namespace Tonic\Tests
 */
class ResourceTester extends UnitTestCase {
    
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
    
    function testNoParametersArgumentToResourceConstructor() {
        
        $config = array(
            'uri' => '/resourcetest/badconstructor'
        );
        
        $this->expectError(new PatternExpectation('/Missing argument 1 for Resource::__construct/'));
        $this->expectError(new PatternExpectation('/Undefined variable: parameters/'));
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
    }
    
    function testMethodDoesNotReturnResponseObject() {
        
        $config = array(
            'uri' => '/resourcetest/badmethodresponse'
        );
        
        $this->expectException(new PatternExpectation('/Method GET of (.+\\\\)?TestBadMethodResponse did not return a Response object/'));
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        
    }

}

