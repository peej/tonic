<?php

require_once dirname(__FILE__).'/../src/Tonic/Autoloader.php';

/**
 * @uri /baz/:foo
 * @priority 10
 * @namespace myNamespace
 */
class MyOtherResource extends Tonic\Resource {

    /**
     * @method GET
     * @accepts application/x-www-form-urlencoded
     * @accepts application/multipart
     * @provides text/html
     * @myCondition
     * @beforeCondition
     * @afterCondition
     * @parameterCondition param1 "param 2"
     */
    function myMethod()
    {
        return array(200, 'Hello');
    }

    /**
     * @method GET
     * @hasQuerystring foo
     */
    function otherMethod()
    {
        return array(200, 'Goodbye');
    }

    function myCondition()
    {
        $this->xyzzy = 'thud';
        if (isset($_GET['error'])) throw new Tonic\ConditionException;
        $this->wibble = 'wobble';
    }

    function beforeCondition()
    {
        $this->before(function ($request) {
            $request->foo = 'bar';
        });
    }

    function afterCondition()
    {
        $this->after(function ($response) {
            $response->baz = 'quux';
        });
    }

    function hasQuerystring($name)
    {
        if (!isset($_GET[$name])) throw new Tonic\ConditionException;
        return true;
    }

    function parameterCondition($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }

}

/**
 * @uri /quux
 */
class MyResourceWide extends Tonic\Resource {

    protected function setup()
    {
        $this->before(function ($request) {
            $request->foo = 'bar';
        });
        $this->after(function ($response) {
            $response->baz = 'quux';
        });
    }

    /**
     * @method GET
     * @provides something
     */
    function something()
    {
        return 'Something';
    }

    /**
     * @method GET
     * @provides otherthing
     */
    function otherthing()
    {
        return 'Otherthing';
    }
}

class DescribeTonicResource extends \PHPSpec\Context
{

    function before()
    {
        unset($_GET);
    }

    private function createResource($request)
    {
        $app = new Tonic\Application;
        return new MyOtherResource($app, $request);
    }

    function itShouldExposeTheUriParameters()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/bar',
            'params' => array('foo' => 'bar')
        ));
        $resource = $this->createResource($request);
        $this->spec(isset($resource->foo))->should->be(true);
        $this->spec($resource->foo)->should->be('bar');
        $this->spec(isset($resource->baz))->should->be(false);
        $this->spec($resource->baz)->should->be(null);
    }

    function itShouldExecuteTheResourceByCallingMyMethod()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'application/x-www-form-urlencoded'
        ));
        $resource = $this->createResource($request);
        $this->spec($resource->exec())->should->beAnInstanceOf('Tonic\Response');
    }

    function itShouldThrowANotAcceptableException()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'application/x-www-form-urlencoded',
            'accept' => 'text/plain'
        ));
        $resource = $this->createResource($request);
        $this->spec(function() use ($resource) {
            $resource->exec();
        })->should->throwException('Tonic\NotAcceptableException');
    }

    function itShouldThrowAMethodNotAllowedException()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'application/x-www-form-urlencoded',
            'method' => 'POST'
        ));
        $resource = $this->createResource($request);
        $this->spec(function() use ($resource) {
            $resource->exec();
        })->should->throwException('Tonic\MethodNotAllowedException');
    }

    function itShouldThrowAnUnsupportedMediaTypeException()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'text/plain'
        ));
        $resource = $this->createResource($request);
        $this->spec(function() use ($resource) {
            $resource->exec();
        })->should->throwException('Tonic\UnsupportedMediaTypeException');
    }

    function itShouldExecuteTheBeforeAndAfterConditions()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'application/x-www-form-urlencoded'
        ));
        $resource = $this->createResource($request);
        $response = $resource->exec();
        $this->spec($request->foo)->should->be('bar');
        $this->spec($response->baz)->should->be('quux');
    }

    function itShouldExecuteTheResourceWideBeforeAndAfterConditions()
    {
        $request = new Tonic\Request(array(
            'uri' => '/quux',
            'contentType' => 'application/x-www-form-urlencoded'
        ));
        $app = new Tonic\Application;
        $resource = new MyResourceWide($app, $request);
        $response = $resource->exec();
        $this->spec($request->foo)->should->be('bar');
        $this->spec($response->body)->should->be('Something');
        $this->spec($response->baz)->should->be('quux');
    }

    function itShouldNeverThrowAConditionException()
    {
        $_GET['error'] = true;
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'application/x-www-form-urlencoded'
        ));
        $resource = $this->createResource($request);
        $this->spec(function() use ($resource) {
            $resource->exec();
        })->shouldNot->throwException('Tonic\ConditionException');
        $this->spec($resource->xyzzy)->should->be('thud');
        $this->spec($resource->wibble)->shouldNot->be('wobble');
    }

    function itShouldExecuteTheBestMatchingResourceMethod()
    {
        $_GET['foo'] = true;
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux'
        ));
        $resource = $this->createResource($request);
        $response= $resource->exec();
        $this->spec($response->body)->should->be('Goodbye');
    }

    function itShouldHandleConditionParametersContainingSpaces()
    {
        $request = new Tonic\Request(array(
            'uri' => '/baz/quux',
            'contentType' => 'application/x-www-form-urlencoded'
        ));
        $resource = $this->createResource($request);
        $this->spec(function() use ($resource) {
            $resource->exec();
        });
        $this->spec($resource->param1)->should->be('param1');
        $this->spec($resource->param2)->should->be('param 2');
    }

}