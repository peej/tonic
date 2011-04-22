<?php

require_once('def/response.php');

/**
 * @namespace Tonic\Tests
 */
class ResponseTester extends UnitTestCase {
    
    function testDefaultCacheHeader() {
        
        $config = array(
            'uri' => '/responsetest'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->addCacheHeader();
        
        $this->assertEqual($response->headers['Cache-Control'], 'max-age=86400, must-revalidate');
        
    }
    
    function testNoCacheHeader() {
        
        $config = array(
            'uri' => '/responsetest'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->addCacheHeader(0);
        
        $this->assertEqual($response->headers['Cache-Control'], 'no-cache');
        
    }
    
    function testAddEtag() {
        
        $config = array(
            'uri' => '/responsetest'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->addEtag("123123");
        
        $this->assertEqual($response->headers['Etag'], '"123123"');
        
    }
    
}

