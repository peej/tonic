<?php

namespace spec\Tonic;

use PhpSpec\ObjectBehavior;

class ResourceSpec extends ObjectBehavior
{

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function let($app, $request)
    {
        $this->beConstructedWith($app, $request);
    }

    function it_should_be_initializable()
    {
        $this->shouldHaveType('Tonic\Resource');
    }

    /**
     * @param \Tonic\Request $request
     */
    function it_should_expose_the_uri_parameters($request)
    {
        $request->getParams()->willReturn(array('foo' => 'bar'));
        $this->foo->shouldBe('bar');
        $this->baz->shouldBe(null);
    }

}
