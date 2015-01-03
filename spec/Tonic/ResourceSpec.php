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

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     * @param \Tonic\ResourceMetadata $resourceMetadata
     * @param \Tonic\MethodMetadata $methodMetadata
     */
    function it_shows_options($app, $request, $resourceMetadata, $methodMetadata)
    {
        $request->getMethod()->willReturn('options');
        $request->getParams()->willReturn(array());

        $methodMetadata->getConditions()->willReturn(array(
            'method' => array('options')
        ));
        $resourceMetadata->getMethods()->willReturn(array(
            'options' => $methodMetadata
        ));
        $app->getResourceMetadata(\Prophecy\Argument::any())->willReturn($resourceMetadata);

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->getHeader('Allow')->shouldBe('OPTIONS');
        $response->body->shouldBe(array(
            'OPTIONS'
        ));
    }

}
