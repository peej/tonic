<?php

require_once('def/request.php');

/**
 * @namespace Tonic\Tests
 */
class RequestTester extends UnitTestCase {
    
    function testRequestURI() {
        
        $config = array(
            'uri' => '/requesttest/one/two'
        );
        $request = new Request($config);
        $this->assertEqual($request->uri, $config['uri']);
        
        $_SERVER['REQUEST_URI'] = '/requesttest/one/two';
        $request = new Request();
        $this->assertEqual($request->uri, $config['uri']);
        
    }
    
    function testRequestBaseUri() {
        
        $config = array(
            'baseUri' => '/some/sub/dir'
        );
        
        $request = new Request($config);
        
        $this->assertEqual($request->baseUri, $config['baseUri']);
        
    }
    
    function testAssignMimetypes() {
        
        $config = array(
            'mimetypes' => array(
                'some' => 'text/something'
            )
        );
        $request = new Request($config);
        $this->assertEqual($request->mimetypes['some'], 'text/something');
        
    }
    
    function testGetRequestMethod() {
        
        $config = array();
        
        $request = new Request($config);
        
        $this->assertEqual($request->method, 'GET');
        
    }
    
    function testPostRequestMethodWithData() {
        
        $config = array(
            'method' => 'post',
            'data' => 'some data'
        );
        
        $request = new Request($config);
        
        $this->assertEqual($request->method, 'POST');
        $this->assertEqual($request->data, 'some data');
        
    }
    
    function testPutRequestMethodWithData() {
        
        $config = array(
            'method' => 'put',
            'data' => 'some data'
        );
        
        $request = new Request($config);
        
        $this->assertEqual($request->method, 'PUT');
        $this->assertEqual($request->data, 'some data');
        
    }
    
    function testConnegOnBareURI() {
        
        $config = array(
            'uri' => '/requesttest/one/two',
            'accept' => '',
            'acceptLang' => ''
        );
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
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
        
        $request = new Request($config);
        
        try {
            $resource = $request->loadResource();
            $this->fail('Expected ResponseException to be thrown');
        } catch (ResponseException $e) {
            $this->assertEqual($e->getCode(), 404);
        }
        
    }
    
    function testResourceLoaderWithAResources() {
        
        $config = array(
            'uri' => '/requesttest/one'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/NewResource/', get_class($resource));
        
    }
    
    function testResourceLoaderWithAChildResources() {
        
        $config = array(
            'uri' => '/requesttest/one/two'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/ChildResource/', get_class($resource));
        
    }
    
    function testResourceLoaderWithRegexURIMatch() {
        
        $config = array(
            'uri' => '/requesttest/three/something/four'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/NewResource/', get_class($resource));
        $this->assertPattern('/0: something/', $resource);
        
    }
    
    function testIfMatch() {
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '123123'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '"123123"'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '"123123","456456"'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifMatch, array('123123', '456456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '"123123", "456456"'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifMatch, array('123123', '456456'));
        $this->assertTrue($request->ifMatch('123123'));
        $this->assertFalse($request->ifMatch('123456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifMatch' => '*'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifMatch, array('*'));
        $this->assertTrue($request->ifMatch('123123'));
        $this->assertTrue($request->ifMatch('123456'));
        
    }
    
    function testIfNoneMatch() {
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '123123'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '"123123"'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '"123123","456456"'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123', '456456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '123123, 456456'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifNoneMatch, array('123123', '456456'));
        $this->assertTrue($request->ifNoneMatch('123123'));
        $this->assertFalse($request->ifNoneMatch('123456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '*'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifNoneMatch, array('*'));
        $this->assertFalse($request->ifNoneMatch('123123'));
        $this->assertFalse($request->ifNoneMatch('123456'));
        
        $config = array(
            'uri' => '/requesttest/one',
            'ifNoneMatch' => '123123, 456456',
            'ifMatch' => '*'
        );
        $request = new Request($config);
        $this->assertEqual($request->ifMatch, array('*'));
        $this->assertTrue($request->ifMatch('123123'));
        $this->assertTrue($request->ifMatch('123456'));
        $this->assertFalse($request->ifNoneMatch('123123'));
        $this->assertFalse($request->ifNoneMatch('123456'));
        
    }
    
    function test404NotFound() {
        
        $config = array(
            'uri' => '/requesttest/notfound'
        );
        $request = new Request($config);
        
        try {
            $resource = $request->loadResource();
            $this->fail('Expected ResponseException to be thrown');
        } catch (ResponseException $e) {
            $this->assertEqual($e->getCode(), 404);
        }
        
    }
    
    function testResourceDataLoading() {
        
        $config = array(
            'uri' => '/requesttest/one/two'
        );
        
        $request = new Request($config);
        
        $this->assertEqual($request->resources['/requesttest/one/two']['namespace'], 'Tonic\Tests');
        $this->assertPattern('/ChildResource/', $request->resources['/requesttest/one/two']['class']);
        $this->assertPattern('|def/request\.php|', $request->resources['/requesttest/one/two']['filename']);
        $this->assertEqual($request->resources['/requesttest/one/two']['priority'], 0);
        
    }
    
    function testNamespaceMounting() {
        
        $config = array(
            'uri' => '/foo/bar/requesttest/one',
            'mount' => array(
                'Tonic\Tests' => '/foo/bar'
            )
        );
        
        $request = new Request($config);
        
        $resource = $request->loadResource();
        
        $this->assertPattern('/NewResource/', get_class($resource));
        
    }
    
    function testURITemplateStyleUriParameters() {
        
        $config = array(
            'uri' => '/requesttest/uritemplatestyle/woo/yay'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/TwoUriParams/', get_class($resource));
        $this->assertPattern('/param: woo/', $resource);
        $this->assertPattern('/param2: yay/', $resource);
        
        $resource->exec($request);
        $this->assertEqual($resource->receivedParams['param'], 'woo');
        $this->assertEqual($resource->receivedParams['param2'], 'yay');
        
        
        $config = array(
            'uri' => '/requesttest/uritemplatemixedstyle/woo/yay/foo/bar'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/FourUriParams/', get_class($resource));
        $this->assertPattern('/param: woo/', $resource);
        $this->assertPattern('/1: yay/', $resource);
        $this->assertPattern('/param2: foo/', $resource);
        $this->assertPattern('/3: bar/', $resource);
        
        $resource->exec($request);
        $this->assertEqual($resource->receivedParams['param'], 'woo');
        $this->assertEqual($resource->receivedParams['something'], 'yay');
        $this->assertEqual($resource->receivedParams['param2'], 'foo');
        $this->assertEqual($resource->receivedParams['otherthing'], 'bar');
    }
    
    function testRailsStyleUriParameters() {
        
        $config = array(
            'uri' => '/requesttest/railsstyle/woo/yay'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/TwoUriParams/', get_class($resource));
        $this->assertPattern('/param: woo/', $resource);
        $this->assertPattern('/param2: yay/', $resource);
        
        $resource->exec($request);
        $this->assertEqual($resource->receivedParams['param'], 'woo');
        $this->assertEqual($resource->receivedParams['param2'], 'yay');
        
        
        $config = array(
            'uri' => '/requesttest/railsmixedstyle/woo/yay/foo/bar'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/FourUriParams/', get_class($resource));
        $this->assertPattern('/param: woo/', $resource);
        $this->assertPattern('/1: yay/', $resource);
        $this->assertPattern('/param2: foo/', $resource);
        $this->assertPattern('/3: bar/', $resource);
        
        $resource->exec($request);
        $this->assertEqual($resource->receivedParams['param'], 'woo');
        $this->assertEqual($resource->receivedParams['something'], 'yay');
        $this->assertEqual($resource->receivedParams['param2'], 'foo');
        $this->assertEqual($resource->receivedParams['otherthing'], 'bar');
    }
    
    function testMixedStyleUriParameters() {
        
        $config = array(
            'uri' => '/requesttest/mixedstyle/woo/yay/foo/bar'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/FourUriParams/', get_class($resource));
        $this->assertPattern('/param: woo/', $resource);
        $this->assertPattern('/1: yay/', $resource);
        $this->assertPattern('/param2: foo/', $resource);
        $this->assertPattern('/3: bar/', $resource);
        
        $resource->exec($request);
        $this->assertEqual($resource->receivedParams['param'], 'woo');
        $this->assertEqual($resource->receivedParams['something'], 'yay');
        $this->assertEqual($resource->receivedParams['param2'], 'foo');
        $this->assertEqual($resource->receivedParams['otherthing'], 'bar');
    }
    
    function testAutoloadingOfResourcesWithoutAutoloadFunction() {
        
        $config = array(
            'uri' => '/requesttest/autoload',
            'autoload' => array(
                '/requesttest/autoload' => 'Autoload'
            )
        );
        
        $request = new Request($config);
        
        $this->expectException(new PatternExpectation('/Unable to load resource/'));
        $resource = $request->loadResource();
        
    }
    
    function testAutoloadingOfResources() {
        
        $config = array(
            'uri' => '/requesttest/autoload',
            'autoload' => array(
                '/requesttest/autoload' => 'Autoload'
            )
        );
        
        function __autoload($className) {
            if (file_exists('def/'.$className.'.php')) {
                require_once 'def/'.$className.'.php';
            }
        }
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/Autoload/', get_class($resource));
        $this->assertPattern('/Class: Autoload/', $request);
        $this->assertPattern('|File: '.dirname(__FILE__).DIRECTORY_SEPARATOR.'def/Autoload.php|', $request);
        
    }
    
    function testTrailingSlashRemoval() {
        
        $config = array(
            'uri' => '/requesttest/trailingslashurl/'
        );
        $request = new Request($config);
        $this->assertEqual($request->uri, substr($config['uri'], 0, -1));
        
    }
    
    function testRequestRootURI() {
        
        $config = array(
            'uri' => '/'
        );
        $request = new Request($config);
        $this->assertEqual($request->uri, $config['uri']);
        
        $_SERVER['REQUEST_URI'] = '/';
        $request = new Request();
        $this->assertEqual($request->uri, $config['uri']);
        
    }
    
    function testMethodNotAllowed() {
        
        $config = array(
            'uri' => '/requesttest/one',
            'method' => 'PUT'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        try {
            $response = $resource->exec($request);
            $this->fail('Expected ResponseException to be thrown');
        } catch (ResponseException $e) {
            $this->assertEqual($e->getCode(), 405);
        }
        
    }
    
    function testOptionalRegexURIParams() {
        
        $config = array(
            'uri' => '/requesttest/optional'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/OptionalParams/', get_class($resource));
        $this->assertNoPattern('/0: /', $resource);
        
        $config = array(
            'uri' => '/requesttest/optional/param1/woo'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/OptionalParams/', get_class($resource));
        $this->assertPattern('/0: woo/', $resource);
        
        $config = array(
            'uri' => '/requesttest/optional/something'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/OptionalParams/', get_class($resource));
        $this->assertPattern('/0: something/', $resource);
        
    }
    
    function testResourceLoaderWithSquigglyBraceRegexURIMatch() {
        
        $config = array(
            'uri' => '/requesttest/squiggly/9999-aaaaaaaa-bbbbbb'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/SquigglyRegexResource/', get_class($resource));
        
        $config = array(
            'uri' => '/requesttest/squiggly/9999-aaa-bbb'
        );
        
        $request = new Request($config);
        
        try {
            $resource = $request->loadResource();
            $this->fail('Expected ResponseException to be thrown');
        } catch (ResponseException $e) {
            $this->assertTrue(TRUE);
        }
        
    }
    
    function testResourceLoaderWithNonCapturingGroupRegexURIMatch() {
        
        $config = array(
            'uri' => '/requesttest/noncapture/something'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertPattern('/SquigglyRegexResource/', get_class($resource));
        
    }
    
    function testAllowedHTTPMethods() {
        
        $config = array(
            'uri' => '/requesttest/httpmethods',
            'method' => 'OPTIONS'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        try {
            $response = $resource->exec($request);
            $this->assertTrue(TRUE);
        } catch (ResponseException $e) {
            $this->fail('Did not expect ResponseException to be thrown');
        }
        
        $config = array(
            'uri' => '/requesttest/httpmethods',
            'method' => 'WOOT'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        try {
            $response = $resource->exec($request);
            $this->fail('Expected ResponseException to be thrown');
        } catch (ResponseException $e) {
            $this->assertTrue(TRUE);
        }
        
    }
    
    function testURIPriority() {
        
        // with same @uri
        
        $config = array(
            'uri' => '/requesttest/priority'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'PriorityTestMoreImportantResource');
        
        // with different @uri
        
        $config = array(
            'uri' => '/requesttest/differenturipriority'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'PriorityTestDifferntURIMoreImportantResource');
        
    }
    
}

