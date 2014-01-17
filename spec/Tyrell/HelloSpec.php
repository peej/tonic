<?php

namespace spec\Tyrell;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HelloSpec extends ObjectBehavior
{

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     * @param \Tonic\ResourceMetadata $resourceMetadata
     */
    function let($app, $request, $resourceMetadata, $sayHello, $sayHelloInFrench, $replicants, $iveSeenThings, $sayHelloComputer, $feedTheComputer)
    {
        $request->getMethod()->willReturn('GET');
        $request->getParams()->willReturn(array());
        $request->getAccept()->willReturn(array());
        $request->getAcceptLanguage()->willReturn(array());
        
        $sayHello->beADoubleOf('\Tonic\MethodMetadata');
        $sayHello->getConditions()->willReturn(array(
            'method' => array('GET')
        ));

        $sayHelloInFrench->beADoubleOf('\Tonic\MethodMetadata');
        $sayHelloInFrench->getConditions()->willReturn(array(
            'method' => array('GET'),
            'lang' => array('fr')
        ));

        $replicants->beADoubleOf('\Tonic\MethodMetadata');
        $replicants->getConditions()->willReturn(array(
            'method' => array('GET'),
            'priority' => array(2),
            'only' => array('deckard')
        ));

        $iveSeenThings->beADoubleOf('\Tonic\MethodMetadata');
        $iveSeenThings->getConditions()->willReturn(array(
            'method' => array('GET'),
            'priority' => array(2),
            'only' => array('roy')
        ));

        $sayHelloComputer->beADoubleOf('\Tonic\MethodMetadata');
        $sayHelloComputer->getConditions()->willReturn(array(
            'method' => array('GET'),
            'provides' => array('application/json'),
            'json' => array()
        ));

        $feedTheComputer->beADoubleOf('\Tonic\MethodMetadata');
        $feedTheComputer->getConditions()->willReturn(array(
            'method' => array('POST'),
            'accepts' => array('application/json'),
            'provides' => array('application/json'),
            'json' => array()
        ));

        $resourceMetadata->getClass()->willReturn('\\Tyrell\\Hello');
        $resourceMetadata->getUri()->willReturn(array(array('/hello')));
        $resourceMetadata->getMethod('setup')->willReturn(null);
        $resourceMetadata->getMethods()->willReturn(array(
            'sayHello' => $sayHello,
            'sayHelloInFrench' => $sayHelloInFrench,
            'replicants' => $replicants,
            'iveSeenThings' => $iveSeenThings,
            'sayHelloComputer' => $sayHelloComputer,
            'feedTheComputer' => $feedTheComputer
        ));
        $resourceMetadata->getMethod('setup')->willReturn(null);
        $app->getResourceMetadata(\Prophecy\Argument::any())->willReturn($resourceMetadata);
        
        $this->beConstructedWith($app, $request);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Tyrell\Hello');
        $this->shouldHaveType('Tonic\Resource');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_say_hello_world($app, $request)
    {
        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('Hello World');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_say_hello_parameter($app, $request)
    {
        $request->getParams()->willReturn(array('name' => 'test'));

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('Hello Test');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_say_bonjour($app, $request)
    {
        $request->getAcceptLanguage()->willReturn(array('fr'));

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('Bonjour Monde');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_say_bonjour_parameter($app, $request)
    {
        $request->getParams()->willReturn(array('name' => 'test'));
        $request->getAcceptLanguage()->willReturn(array('fr'));

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('Bonjour Test');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_respond_to_deckard($app, $request)
    {
        $request->getParams()->willReturn(array('name' => 'deckard'));

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('Replicants are like any other machine - they\'re either a benefit or a hazard.');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_respond_to_roy($app, $request)
    {
        $request->getParams()->willReturn(array('name' => 'roy'));

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->body->shouldBe('I\'ve seen things you people wouldn\'t believe.');
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_say_hello_computer($app, $request)
    {
        $app->uri(Argument::any(), Argument::any())->willReturn('/hello');
        $request->getAccept()->willReturn(array('application/json'));
        $request->getContentType()->willReturn('application/json');
        $request->getData()->willReturn(null);
        $request->setData(Argument::any())->willReturn(null);

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->contentType->shouldBe('application/json');
        $response->body->shouldBe(json_encode(array(
            'hello' => null,
            'url' => '/hello'
        )));
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_should_feed_the_computer($app, $request)
    {
        $request->getMethod()->willReturn('POST');
        $request->getContentType()->willReturn('application/json');
        $request->getData()->willReturn('{"hello": "computer"}');
        $request->setData(Argument::any())->will(function ($args) use ($request) {
            $request->getData()->willReturn($args[0]);
        });
        $request->getAccept()->willReturn(array('application/json'));

        $response = $this->exec();

        $response->code->shouldBe(200);
        $response->contentType->shouldBe('application/json');
        $response->body->shouldBe(json_encode(array(
            'hello' => 'computer'
        )));
    }

    /**
     * @param \Tonic\Application $app
     * @param \Tonic\Request $request
     */
    function it_can_turn_itself_into_a_string($app, $request)
    {
        $this->__toString()->shouldBe(<<<EOF
==============
Tonic\Resource
==============
Class: \Tyrell\Hello
URI regex: /hello
Params: 
Methods: 
\t[1] sayHello
\t[Tonic\NotAcceptableException 1] sayHelloInFrench
\t[-] replicants
\t[-] iveSeenThings
\t[1] sayHelloComputer
\t[Tonic\MethodNotAllowedException 0] feedTheComputer

EOF
);
    }
}
