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
        $this->headers['foo-bar']->shouldBe('baz');
    }

}
