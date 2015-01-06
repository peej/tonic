<?php

namespace spec\Tonic;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResourceMetadataSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->shouldHaveType('Tonic\ResourceMetadata');
    }

    function it_should_retrieve_the_classes_uri()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getUri()->shouldHaveCount(4);
        $uris = $this->getUri();
        $uris[0]->shouldBe('/url1');
        $this->getUri(0)->shouldBe('/url1');
        $this->getUri(1)->shouldBe('/url2');
        $this->getUri(2)->shouldBe('/url3 /url4');
        $this->getUri(3)->shouldBe('/url5/([^/]+)');
        $this->getUri(4)->shouldBe(null);
    }

    function it_should_retrieve_the_classes_namespace_from_php()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getNamespace()->shouldBe('spec\Tonic');
    }

    function it_should_retrieve_the_classes_namespace_from_doccomment()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass2');
        $this->getNamespace()->shouldBe('exampleNamespace');
    }

    function it_should_retrieve_the_classes_priority()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getPriority()->shouldBe(4);
    }

    function it_should_retrieve_the_classes_url_params()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getUri(3)->shouldBe('/url5/([^/]+)');
        $this->getUriParams(3)->shouldBe(array('something'));
    }

    function it_should_retrieve_the_classes_filename()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getFilename()->shouldBe(__FILE__);
    }

    function it_should_retrieve_the_classes_class()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getClass()->shouldBe('\spec\Tonic\exampleClass');
    }

    function it_should_retrieve_the_classes_methods()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getMethod('exampleMethod')->shouldHaveType('Tonic\MethodMetadata');
        $this->getMethod('nonExistantMethod')->shouldBe(null);
    }

    function it_should_mount_a_class_at_a_urispace()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass');
        $this->getUri()->shouldHaveCount(4);
        $this->getUri(0)->shouldBe('/url1');
        $this->mount('/woo/yay');
        $this->getUri(0)->shouldBe('/woo/yay/url1');
    }

}

/**
 *  Example class
 *
 * @uri /url1
 * @uri /url2
 * @uri /url3 /url4
 * @url /url5/:something
 * @priority 4 10
 * @priority 6
 */
class exampleClass {
    function exampleMethod() {}
}

/***
 * @namespace exampleNamespace
 */
class exampleClass2 {}