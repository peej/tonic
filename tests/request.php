<?php

require_once('../lib/tonic.php');

class RequestTester extends UnitTestCase {
    
    function testRequestURI() {
        
        $config = array(
            'uri' => '/one/two'
        );
        
        $request = new Request($config);
        
        $this->assertEqual($request->uri, $config['uri']);
        
    }
    
    function testConnegOnBareURI() {
        
        $config = array(
            'uri' => '/one/two',
            'accept' => '',
            'acceptLang' => ''
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two'
        ));
    
    }
    
    function testConnegOnExtensionURI() {
        
        $config = array(
            'uri' => '/one/two.html',
            'accept' => '',
            'acceptLang' => ''
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.html',
            '/one/two'
        ));
        
    }
    
    function testConnegOnBareURIWithAccept() {
        
        $config = array(
            'uri' => '/one/two',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => ''
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.html',
            '/one/two.png',
            '/one/two'
        ));
        
    }
    
    function testConnegOnExtensionURIWithAccept() {
        
        $config = array(
            'uri' => '/one/two.html',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => ''
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.html',
            '/one/two.png.html',
            '/one/two.png',
            '/one/two'
        ));
        
    }
    
    function testConnegOnBareURIWithAcceptLang() {
        
        $config = array(
            'uri' => '/one/two',
            'accept' => '',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.en',
            '/one/two.fr',
            '/one/two'
        ));
        
    }
    
    function testConnegOnExtensionURIWithAcceptLang() {
        
        $config = array(
            'uri' => '/one/two.html',
            'accept' => '',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.html.en',
            '/one/two.html.fr',
            '/one/two.html',
            '/one/two.en',
            '/one/two.fr',
            '/one/two'
        ));
        
    }
    
    function testConnegOnBareURIWithAcceptAndAcceptLang() {
        
        $config = array(
            'uri' => '/one/two',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.html.en',
            '/one/two.html.fr',
            '/one/two.html',
            '/one/two.png.en',
            '/one/two.png.fr',
            '/one/two.png',
            '/one/two.en',
            '/one/two.fr',
            '/one/two'
        ));
        
    }
    
    function testConnegOnExtensionURIWithAcceptAndAcceptLang() {
        
        $config = array(
            'uri' => '/one/two.html',
            'accept' => 'image/png;q=0.5,text/html',
            'acceptLang' => 'fr;q=0.5,en'
        );
        
        $request = new Request($config);
        $this->assertEqual($request->uris, array(
            '/one/two.html.en',
            '/one/two.html.fr',
            '/one/two.html',
            '/one/two.png.html',
            '/one/two.png.en',
            '/one/two.png.fr',
            '/one/two.png',
            '/one/two.en',
            '/one/two.fr',
            '/one/two'
        ));
        
    }
    
    function testResourceLoaderWithNoResources() {
        
        $config = array(
            'uri' => '/three'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'Resource');
        
    }
    
    function testResourceLoaderWithAResources() {
        
        $config = array(
            'uri' => '/one'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'NewResource');
        
    }
    
    function testResourceLoaderWithAChildResources() {
        
        $config = array(
            'uri' => '/one/two'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'ChildResource');
        
    }
    
    function testResourceLoaderWithRegexURIMatch() {
        
        $config = array(
            'uri' => '/three/something/four'
        );
        
        $request = new Request($config);
        $resource = $request->loadResource();
        
        $this->assertEqual(get_class($resource), 'NewResource');
        
    }
    
}


/* Test resource definitions */

/**
 * @uri /one
 * @uri /three/.+/four 12
 */
class NewResource extends Resource {

}

/**
 * @uri /one/two
 */
class ChildResource extends NewResource {

}


?>
