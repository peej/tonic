<?php

require_once dirname(__FILE__).'/../src/Tonic/Autoloader.php';

class DescribeTonicResponse extends \PHPSpec\Context
{

    function itShouldHaveADefaultResponseCode()
    {
        $response = new Tonic\Response;
        $this->spec($response->code)->should->be(204);
    }

    function itShouldBeCreatableViaTheFactoryWithJustAStatusCode()
    {
        $response = Tonic\Response::create(204);
        $this->spec($response->code)->should->be(204);
        $this->spec($response->body)->should->be(null);
    }

    function itShouldBeCreatableViaTheFactoryWithJustABody()
    {
        $response = Tonic\Response::create('body');
        $this->spec($response->code)->should->be(200);
        $this->spec($response->body)->should->be('body');
    }

    function itShouldBeCreatableViaTheFactoryWithBothACodeAndABody()
    {
        $response = Tonic\Response::create(array(204, 'body'));
        $this->spec($response->code)->should->be(204);
        $this->spec($response->body)->should->be('body');
    }

    function itShouldAllowHeadersToBeAdded()
    {
        $response = new Tonic\Response;
        $response->myHeader = 'woo';
        $this->spec($response->headers['my-header'])->should->be('woo');
    }

    function itShouldAllowHeadersToBeAddedWhichStartWithCaps()
    {
        $response = new Tonic\Response;
        $response->MyHeaderIsMuchlyCamelCase = 'woo';
        $this->spec($response->headers['my-header-is-muchly-camel-case'])->should->be('woo');
    }

}