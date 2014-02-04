<?php

namespace spec\Tyrell;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SecretSpec extends ObjectBehavior
{

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     * @param \Tonic\ResourceMetadata $resourceMetadata
     */
    function let($app, $request, $resourceMetadata, $mySecret)
    {
        $request->getMethod()->willReturn('GET');
        $request->getParams()->willReturn(array());
        $request->getAccept()->willReturn(array());
        $request->getAcceptLanguage()->willReturn(array());
        
        $mySecret->beADoubleOf('\Tonic\MethodMetadata');
        $mySecret->getConditions()->willReturn(array(
            'method' => array('GET'),
            'secure' => array('aUser aPassword')
        ));

        $resourceMetadata->getClass()->willReturn('\\Tyrell\\Secret');
        $resourceMetadata->getUri()->willReturn(array(array('/secret')));
        $resourceMetadata->getMethod('setup')->willReturn(null);
        $resourceMetadata->getMethods()->willReturn(array(
            'mySecret' => $mySecret
        ));
        $resourceMetadata->getMethod('setup')->willReturn(null);
        $app->getResourceMetadata(\Prophecy\Argument::any())->willReturn($resourceMetadata);
        
        $this->beConstructedWith($app, $request);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Tyrell\Secret');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_secure_the_secret($app, $request)
    {
        $this->shouldThrow('\Tonic\UnauthorizedException')->duringExec();
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_return_the_secret($app, $request)
    {
        $_SERVER['PHP_AUTH_USER'] = 'aUser';
        $_SERVER['PHP_AUTH_PW'] = 'aPassword';

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('My secret');

        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SERVER['PHP_AUTH_PW']);
    }
}
