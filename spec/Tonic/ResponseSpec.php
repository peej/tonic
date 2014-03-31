<?php

namespace spec\Tonic;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResponseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Tonic\Response');
    }

    function it_can_be_constructed_naked()
    {
        $this->code->shouldBe(204);
        $this->body->shouldBe(null);
        $this->headers['content-type']->shouldBe('text/html');
    }

    function it_can_be_constructed_with_just_a_body()
    {
        $this->beConstructedWith('body');

        $this->code->shouldBe(200);
        $this->body->shouldBe('body');
    }

    function it_can_be_constructed_with_headers()
    {
        $this->beConstructedWith(200, 'body', array(
            'foo-bar' => 'baz'
        ));

        $this->code->shouldBe(200);
        $this->body->shouldBe('body');
        $this->getHeader('foo-bar')->shouldBe('baz');
    }

    function it_allows_headers_to_be_added()
    {
        $this->myHeader = 'woo';
        $this->getHeader('My-Header')->shouldBe('woo');
        $this->myHeader->shouldBe('woo');
    }

    function it_allows_headers_to_be_added_with_caps()
    {
        $this->MyHeaderIsMuchlyCamelCase = 'woo';
        $this->getHeader('My-Header-Is-Muchly-Camel-Case')->shouldBe('woo');
        $this->myHeaderIsMuchlyCamelCase->shouldBe('woo');
    }

    function it_allows_abbreviated_headers_to_be_added()
    {
        $this->contentMD5 = 'foo';
        $this->getHeader('Content-MD5')->shouldBe('foo');
        $this->contentMD5->shouldBe('foo');
        
        $this->contentMd5 = 'bar';
        $this->getHeader('Content-Md5')->shouldBe('bar');
        $this->contentMd5->shouldBe('bar');
        
        $this->ContentMD5 = 'baz';
        $this->getHeader('Content-MD5')->shouldBe('baz');
        $this->ContentMD5->shouldBe('baz');

        $this->ContentMaD5 = 'qux';
        $this->getHeader('Content-Ma-D5')->shouldBe('qux');
        $this->ContentMaD5->shouldBe('qux');
    }

}
