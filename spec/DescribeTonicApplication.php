<?php

require_once dirname(__FILE__).'/../src/Tonic/Autoloader.php';

/**
 * @uri /foo/bar
 * @priority 10
 * @namespace myNamespace
 */
class MyResource extends Tonic\Resource {

    /**
     * @method GET
     * @accepts application/x-www-form-urlencoded
     * @accepts application/multipart
     * @provides text/html
     * @myCondition
     * @param  str $name
     * @return Response
     */
    function myMethod() {
        return array(200, 'Hello');
    }

    function myCondition() {
        return TRUE;
    }

}

class DescribeTonicApplication extends \PHPSpec\Context
{ 

    private $app = null,
            $request = null;

    public function before()
    {
        $this->app = new Tonic\Application;
        $this->request = new Tonic\Request(array(
            'uri' => '/foo/bar'
        ));
    }
  
    public function itShouldLoadAResource()
    {
        $this->spec($this->app->getResource($this->request))->should->beAnInstanceOf('MyResource');
    }

    public function ItShouldGetMetadataAboutAResource()
    {
        $metadata = $this->app->getResourceMetadata('MyResource');
        $this->spec($metadata['class'])->should->be('\\MyResource');
        $this->spec($metadata['uri'][0][0])->should->be('/foo/bar');
        $this->spec($metadata['priority'][0])->should->be('10');
        $this->spec($metadata['namespace'][0])->should->be('myNamespace');
        $this->spec($metadata['methods']['myMethod']['method'][0][0])->should->be('GET');
        $this->spec($metadata['methods']['myMethod']['accepts'])->should->be(array(
            array('application/x-www-form-urlencoded'), array('application/multipart')
        ));
        $this->spec($metadata['methods']['myMethod']['provides'][0][0])->should->be('text/html');
        $this->spec($metadata['methods']['myMethod']['myCondition'])->shouldNot->beNull();
    }

    public function ItShouldBeAbleToMountANamespaceToAUri()
    {
        $this->app->mount('myNamespace', '/baz');
        $metadata = $this->app->getResourceMetadata('MyResource');
        $this->spec($metadata['uri'][0][0])->should->be('/baz/foo/bar');
    }

    public function ItShouldProduceTheUriToAGivenResource()
    {
        $this->spec($this->app->uri('MyResource'))->should->be('/foo/bar');
    }

    function itShouldThrowANotFoundException()
    {
        $app = $this->app;
        $request = new Tonic\Request(array(
            'uri' => '/foo/quux'
        ));
        $this->spec(function() use ($app, $request) {
            $app->getResource($request);
        })->should->throwException('Tonic\NotFoundException');
    }

}