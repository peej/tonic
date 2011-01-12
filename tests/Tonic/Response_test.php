<?php

require_once ('../lib/Tonic/Response.php');

/**
 * @namespace Tonic\Tests
 */
class ResponseTester extends UnitTestCase {
    
    function testGZipOutputEncoding() {
        
        $config = array(
            'uri' => '/responsetest',
            'acceptEncoding' => 'gzip'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->doContentEncoding();
        
        $this->assertEqual($response->headers['Content-Encoding'], 'gzip');
        $this->assertEqual($response->body, gzencode('test'));
        
    }
    
    function testDeflateOutputEncoding() {
        
        $config = array(
            'uri' => '/responsetest',
            'acceptEncoding' => 'deflate'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->doContentEncoding();
        
        $this->assertEqual($response->headers['Content-Encoding'], 'deflate');
        $this->assertEqual($response->body, gzdeflate('test'));
        
    }
    
    function testCompressOutputEncoding() {
        
        $config = array(
            'uri' => '/responsetest',
            'acceptEncoding' => 'compress'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->doContentEncoding();
        
        $this->assertEqual($response->headers['Content-Encoding'], 'compress');
        $this->assertEqual($response->body, gzcompress('test'));
        
    }
    
    function testDefaultCacheHeader() {
        
        $config = array(
            'uri' => '/responsetest'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->addCacheHeader();
        
        $this->assertEqual($response->headers['Cache-Control'], 'max-age=86400, must-revalidate');
        
    }
    
    function testNoCacheHeader() {
        
        $config = array(
            'uri' => '/responsetest'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->addCacheHeader(0);
        
        $this->assertEqual($response->headers['Cache-Control'], 'no-cache');
        
    }
    
    function testAddEtag() {
        
        $config = array(
            'uri' => '/responsetest'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        $response = $resource->exec($request);
        $response->addEtag("123123");
        
        $this->assertEqual($response->headers['Etag'], '"123123"');
        
    }
    
}


/* Test resource definitions */

/**
 * @namespace Tonic\Tests
 * @uri /responsetest
 */
class TestResponse extends Tonic_Resource {
    
    function get($request) {
        
        $response = new Tonic_Response($request);
        $response->body = 'test';
        return $response;
        
    }
    
}
