<?php

namespace spec\Tonic;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MethodMetadataSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('\spec\Tonic\exampleClass3', 'exampleMethod', array(
            '@method' => array('get', 'post'),
            '@accepts' => array('mimetype'),
            '@provides' => array('mimetype'),
            '@something' => array('otherthing')
        ));
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Tonic\MethodMetadata');
    }

    function it_should_have_methods()
    {
        $this->hasMethod('get')->shouldBe(true);
        $this->hasMethod('post')->shouldBe(true);
        $this->hasMethod('put')->shouldBe(false);
        $this->getMethod()->shouldBe(array('get', 'post'));
    }

    function it_should_have_accepts()
    {
        $this->hasAccepts('mimetype')->shouldBe(true);
        $this->hasAccepts('notAMimetype')->shouldBe(false);
    }

    function it_should_have_provides()
    {
        $this->hasProvides('mimetype')->shouldBe(true);
        $this->hasProvides('notAMimetype')->shouldBe(false);
    }

    function it_should_have_something()
    {
        $this->hasCondition('something', 'otherthing')->shouldBe(true);
    }

    function it_should_be_able_to_add_conditions()
    {
        $this->hasCondition('foo', 'bar')->shouldBe(false);
        $this->hasCondition('foo', 'baz')->shouldBe(false);
        $this->getCondition('foo')->shouldBe(null);
        $this->addCondition('foo', 'bar');
        $this->addCondition('foo', 'baz');
        $this->hasCondition('foo', 'bar')->shouldBe(true);
        $this->hasCondition('foo', 'baz')->shouldBe(true);
        $this->getCondition('foo')->shouldBe(array('bar', 'baz'));
    }

    function it_should_be_able_to_set_conditions()
    {
        $this->addCondition('foo', 'bar');
        $this->setCondition('foo', 'baz');
        $this->getCondition('foo')->shouldBe(array('baz'));
    }

}

class exampleClass3 {
    function method() {}
    function accepts() {}
    function provides() {}
    function something() {}
}