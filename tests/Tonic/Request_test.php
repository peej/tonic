<?php

require_once ('../lib/Tonic/Request.php');
require_once ('../lib/Tonic/Resource.php');
require_once ('../lib/Tonic/NoResource.php');
require_once ('../lib/Tonic/Cache/Type.php');
require_once ('../lib/Tonic/Cache/FileCache.php');
require_once ('../lib/Tonic/Cache/Factory.php');

/**
 * @namespace Tonic\Tests
 */
class RequestTester extends UnitTestCase {
    
    function testRequestURI() {
        
        $config = array(
            'uri' => '/requesttest/one/two'
        );
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->uri, $config['uri']);
        
    }

    function testRequestBaseUri() {
        
        $config = array(
            'baseUri' => '/some/sub/dir'
        );
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->baseUri, $config['baseUri']);
        
    }
    
    function testGetRequestMethod() {
        
        $config = array();
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->method, 'GET');
        
    }
    
    function testPutRequestMethodWithData() {
        
        $config = array(
            'method' => 'put',
            'data' => 'some data'
        );
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->method, 'PUT');
        $this->assertEqual($request->data, 'some data');
        
    }
    
    function testConnegOnBareURI() {
        
        $config = array(
            'uri' => '/requesttest/one/two',
            'accept' => '',
            'acceptLang' => ''
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->uri, '/requesttest/one/two');
    
    }
    
    function testConnegOnExtensionURI() {
        
        $config = array(
            'uri' => '/requesttest/one/two.html',
            'accept' => '',
            'acceptLang' => ''
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->uri, '/requesttest/one/two');
        
    }
    
    function testConnegOnBareURIWithAccept() {
        
        $config = array(
            'uri' => '/requesttest/one/two',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => ''
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.png',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.png',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two'
        ));
        
    }
    
    function testConnegOnExtensionURIWithAccept() {
        
        $config = array(
            'uri' => '/requesttest/one/two.html',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => ''
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.png.html',
            '/requesttest/one/two.png',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.png',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two'
        ));
        
    }
    
    function testConnegOnBareURIWithAcceptLang() {
        
        $config = array(
            'uri' => '/requesttest/one/two',
            'accept' => '',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        
    }
    
    function testConnegOnExtensionURIWithAcceptLang() {
        
        $config = array(
            'uri' => '/requesttest/one/two.html',
            'accept' => '',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.html.en',
            '/requesttest/one/two.html.fr',
            '/requesttest/one/two.html',
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        
    }
    
    function testConnegOnBareURIWithAcceptAndAcceptLang() {
        
        $config = array(
            'uri' => '/requesttest/one/two',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.html.en',
            '/requesttest/one/two.html.fr',
            '/requesttest/one/two.html',
            '/requesttest/one/two.png.en',
            '/requesttest/one/two.png.fr',
            '/requesttest/one/two.png',
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.png',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        
    }
    
    function testConnegOnExtensionURIWithAcceptAndAcceptLang() {
        
        $config = array(
            'uri' => '/requesttest/one/two.html',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Tonic_Request($config);
        $this->assertEqual($request->negotiatedUris, array(
            '/requesttest/one/two.html.en',
            '/requesttest/one/two.html.fr',
            '/requesttest/one/two.html',
            '/requesttest/one/two.png.html',
            '/requesttest/one/two.png.en',
            '/requesttest/one/two.png.fr',
            '/requesttest/one/two.png',
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->formatNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.png',
            '/requesttest/one/two'
        ));
        $this->assertEqual($request->languageNegotiatedUris, array(
            '/requesttest/one/two.html',
            '/requesttest/one/two.en',
            '/requesttest/one/two.fr',
            '/requesttest/one/two'
        ));
        
    }
    
    function testResourceLoaderWithNoResources() {
        
        $config = array(
            'uri' => '/three'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'Tonic_NoResource');
        
    }
    
    function testResourceLoaderWithAResources() {
        
        $config = array(
            'uri' => '/requesttest/one'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'NewResource');
        
    }
    
    function testResourceLoaderWithAChildResources() {
        
        $config = array(
            'uri' => '/requesttest/one/two'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'ChildResource');
        
    }
    
    function testResourceLoaderWithRegexURIMatch() {
        
        $config = array(
            'uri' => '/requesttest/three/something/four'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'NewResource');
        
    }
    
	function testResourceLoaderWithTokens() {
        
        $config = array(
            'uri' => '/requesttest/one/two/aVariable'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'ChildResource');
        $this->assertIsA($resource->parameters, 'array');
        $this->assertEqual($resource->parameters, array('variable'=>'aVariable'));
        
    }
    
	function testResourceLoaderWithMultipleTokensAndStaticValues() {
        
        $config = array(
            'uri' => '/requesttest/one/two/aVariable/static/anotherVariable'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'GrandChildResource');
        $this->assertIsA($resource->parameters, 'array');
        $this->assertEqual($resource->parameters, array('variable'=>'aVariable', 'variable2'=>'anotherVariable'));
        
    }
    
	function testResourceLoaderWithTokensAndRegex() {
        
        $config = array(
            'uri' => '/requesttest/one/two/aVariable/number'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'GrandChildResource');
        $this->assertEqual($resource->parameters, array('aVariable', 'number'));
        
    }
    
    function testIfMatch() {
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '123123'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '"123123"'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '"123123","456456"'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifMatch, array('123123', '456456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '"123123", "456456"'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifMatch, array('123123', '456456'));
        $this->assertTrue($request->ifMatch('123123'));
        $this->assertFalse($request->ifMatch('123456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '*'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifMatch, array('*'));
        $this->assertTrue($request->ifMatch('123123'));
        $this->assertTrue($request->ifMatch('123456'));
        
    }
    
    function testIfNoneMatch() {
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '123123'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '"123123"'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '"123123","456456"'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123', '456456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '123123, 456456'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123', '456456'));
        $this->assertTrue($request->ifNoneMatch('123123'));
        $this->assertFalse($request->ifNoneMatch('123456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '*'
        );
        $request = new Tonic_Request($config);
        $this->assertEqual($request->ifNoneMatch, array('*'));
        $this->assertFalse($request->ifNoneMatch('123123'));
        $this->assertFalse($request->ifNoneMatch('123456'));
        
    }
    
    function testResourceLoaderWithNewNoResourceResource() {
        
        $config = array(
            'uri' => '/three',
            '404' => 'NewNoResource'
        );
        
        $request = new Tonic_Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'NewNoResource');
        
    }
    
    function testResourceLoaderWithBadNoResourceResource() {
        
        $config = array(
            'uri' => '/three',
            '404' => 'NewResource'
        );
        
        try {
            $request = new Tonic_Request($config);
            $resource = $request->loadResource();
        } catch(Exception $e) {
            $this->assertEqual($e->getMessage(), '404 resource "NewResource" must be a subclass of "NoResource"');
        }
        
    }
    
    function testResourceDataLoading() {
        
        $config = array(
            'uri' => '/requesttest/one/two'
        );
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->resources['/requesttest/one/two']['namespace'], 'Tonic\Tests');
        $this->assertEqual($request->resources['/requesttest/one/two']['class'], 'ChildResource');
        $this->assertEqual($request->resources['/requesttest/one/two']['filename'], __FILE__);
        $this->assertEqual($request->resources['/requesttest/one/two']['priority'], 0);
        
    }
    
	function testResourceDataLoadingWithTokens() {
        
        $config = array(
            'uri' => '/requesttest/one/two/aVariable'
        );
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->resources['/requesttest/one/two/([^/]+)']['params'], array('variable'));
        
    }
    
	function testResourceDataLoadingWithTokensAndRegex() {
        
        $config = array(
            'uri' => '/requesttest/one/two/aVariable/number'
        );
        
        $request = new Tonic_Request($config);
        
        $this->assertEqual($request->resources['/requesttest/one/two/([^/]+)/([a-z]+)']['class'], 'GrandChildResource');
        $this->assertEqual($request->resources['/requesttest/one/two/([^/]+)/([a-z]+)']['params'], array('variable'));
        
    }
    
    function testNamespaceMounting() {
        
        $config = array(
            'uri' => '/foo/bar/requesttest/one',
            'mount' => array(
                'Tonic\Tests' => '/foo/bar'
            )
        );
        
        $request = new Tonic_Request($config);
        
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'NewResource');
        
    }
    
}


/* Test resource definitions */

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/one
 * @uri /requesttest/three/.+/four 12
 */
class NewResource extends Tonic_Resource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/one/two
 * @uri /requesttest/one/two/:variable
 */
class ChildResource extends NewResource {

}

/**
 * @namespace Tonic\Tests
 * @uri /requesttest/one/two/:variable/([a-z]+)
 * @uri /requesttest/one/two/:variable/static/:variable2
 */
class GrandChildResource extends NewResource {

}

/**
 * @namespace Tonic\Tests
 */
class NewNoResource extends Tonic_NoResource {

}
