<?php

namespace spec\Tonic;

use PhpSpec\ObjectBehavior;

/**
 * @uri /foo/bar
 * @uri /quux/:quuux
 * @priority 10
 * @namespace myNamespace
 */
class ExampleResource extends \Tonic\Resource
{
    /**
     * @method GET
     * @method PUT
     * @accepts application/x-www-form-urlencoded
     * @accepts application/multipart
     * @provides text/html
     * @myCondition
     * @return Response
     */
    function myMethod()
    {
        return 'Example';
    }

    function myCondition()
    {
        return true;
    }
}

class ApplicationSpec extends ObjectBehavior
{
    function letgo()
    {
        $_SERVER = array();
    }
    
    function it_should_be_initializable()
    {
        $this->shouldHaveType('Tonic\Application');
    }

    /**
     * @param \Tonic\Request $request
     */
    function it_should_load_a_resource($request)
    {
        $request->getUri()->willReturn('/foo/bar');
        $request->getParams()->willReturn(null);
        $request->setParams(array())->willReturn(null);
        $this->getResource($request)->shouldHaveType('Tonic\Resource');
    }

    /**
     * @param \Tonic\Request $request
     */
    function it_should_load_a_resource_with_parameters($request)
    {
        $request->getUri()->willReturn('/quux/baz');
        $params = array(
            0 => 'baz',
            'quuux' => 'baz'
        );
        $request->getParams()->willReturn($params);
        $request->setParams($params)->willReturn(null);
        $resource = $this->getResource($request);
        $resource->quuux->shouldBe('baz');
    }

    function it_should_get_metadata_about_a_resource()
    {
        $metadata = $this->getResourceMetadata('spec\Tonic\ExampleResource');
        $metadata->shouldHaveType('Tonic\ResourceMetadata');
        $metadata->getClass()->shouldBe('\spec\Tonic\ExampleResource');
        $metadata->hasUri('/foo/bar')->shouldBe(true);
        $metadata->hasUri('/quux/something')->shouldBe(true);
        $metadata->getPriority()->shouldBe(10);
        $metadata->getNamespace()->shouldBe('myNamespace');
        $metadata->getMethod('myMethod')->shouldHaveType('Tonic\MethodMetadata');
        $metadata->getMethod('myMethod')->hasMethod('GET')->shouldBe(true);
        $metadata->getMethod('myMethod')->hasAccepts('application/x-www-form-urlencoded')->shouldBe(true);
        $metadata->getMethod('myMethod')->hasAccepts('application/multipart')->shouldBe(true);
        $metadata->getMethod('myMethod')->hasProvides('text/html')->shouldBe(true);
        $metadata->getMethod('myMethod')->getCondition('myCondition')->shouldNotBe(null);
        
        $this->getResourceMetadata(new ExampleResource(new \Tonic\Application, new \Tonic\Request(array('uri' => '/'))))->shouldHaveType('Tonic\ResourceMetadata');
        $this->shouldThrow('\Exception')->duringGetResourceMetadata('spec\Tonic\NotAnExampleResource');
    }

    function it_should_be_able_to_mount_a_namespace_to_a_uri()
    {
        $this->mount('myNamespace', '/baz');
        $metadata = $this->getResourceMetadata('spec\Tonic\ExampleResource');
        $metadata->hasUri('/baz/foo/bar')->shouldBe(true);
    }
    
    function it_should_be_able_to_mount_a_namespace_to_a_uri_during_app_construction()
    {
        $this->beConstructedWith(array(
            'mount' => array('myNamespace' => '/baz')
        ));
        $metadata = $this->getResourceMetadata('spec\Tonic\ExampleResource');
        $metadata->getUri(0)->shouldBe('/baz/foo/bar');
    }

    function it_should_produce_the_uri_to_a_given_resource()
    {
        $this->uri('spec\Tonic\ExampleResource')->shouldBe('/foo/bar');
        $this->uri('spec\Tonic\ExampleResource', array('thing'))->shouldBe('/quux/thing');
        $this->uri('spec\Tonic\ExampleResource', 'thing')->shouldBe('/quux/thing');
        $this->uri(new ExampleResource(new \Tonic\Application, new \Tonic\Request(array('uri' => '/'))))->shouldBe('/foo/bar');
        $this->shouldThrow('\Exception')->duringUri('spec\Tonic\NotAnExampleResource');
    }

    /**
     * @param \Tonic\Request $request
     */
    function it_should_throw_a_not_found_exception($request)
    {
        $request->getUri()->willReturn('/foo/quux');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGetResource($request);
        
        $_SERVER['REQUEST_URI'] = '/foo/quux';
        $this->shouldThrow('\Tonic\NotFoundException')->duringGetResource();
    }

    function it_should_include_urispace_in_resource_uri_when_urispace_mounted()
    {
        $this->mount('myNamespace', '/baz');
        $metadata = $this->getResourceMetadata('spec\Tonic\ExampleResource');
        $metadata->hasUri('/baz/foo/bar')->shouldBe(true);
    }

    function it_should_include_base_uri_in_resource_uri_if_constructed_with_one()
    {
        $this->beConstructedWith(array(
            'baseUri' => '/baseUri'
        ));
        $this->mount('myNamespace', '/baz');
        $this->uri('spec\Tonic\ExampleResource')->shouldBe('/baseUri/baz/foo/bar');
    }
    
    function it_should_include_base_uri_in_resource_uri_if_document_root_is_somewhere_else()
    {
        $_SERVER['DOCUMENT_URI'] = '/baseUri/index.php';
        $this->mount('myNamespace', '/baz');
        $this->uri('spec\Tonic\ExampleResource')->shouldBe('/baseUri/baz/foo/bar');
    }
    
    /**
     * @
     */
    function it_should_output_itself_as_a_string()
    {
        $this->beConstructedWith(array(
            'baseUri' => '/baseUri',
            'load' => 'resources/*.php',
            'mount' => array('myNamespace' => '/baz')
        ));
        
        #var_dump((string)$this->getWrappedObject());
        $this->__toString()->shouldBe("=================
Tonic\Application
=================
Base URI: /baseUri
Load path: resources/*.php
Mount points: myNamespace=\"/baz\"
Annotation cache: 
Loaded resources:
\t\spec\Tonic\ExampleResource /baz/foo/bar, /baz/quux/([^/]+) 10
\t\tmyMethod method=\"GET\" method=\"PUT\" accepts=\"application/x-www-form-urlencoded\" accepts=\"application/multipart\" provides=\"text/html\" myCondition
");
    }
    
}
