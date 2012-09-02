<?php

require_once dirname(__FILE__).'/../src/Tonic/Autoloader.php';

/**
 * @uri /baz/quux
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
    }

}

class DescribeTonicResource extends \PHPSpec\Context
{

    function before()
    {
        unset($_GET);
    }

    private function createResource($request = NULL)
    {
        $app = new Tonic\Application;
        if (!$request) {
            $request = new Tonic\Request(array(
                'uri' => '/baz/quux'
            ));
        }
        return new MyOtherResource($app, $request, array(
            'foo' => 'bar'
        ));
    }

    function itShouldExposeTheUriParameters()
    {
        $resource = $this->createResource();
        $this->spec($resource->foo)->should->be('bar');
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

}