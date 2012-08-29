<?php

require_once dirname(__FILE__).'/../src/Tonic/Autoloader.php';

class DescribeTonicRequest extends \PHPSpec\Context
{ 

    function itShouldHaveTheRequestUriFromOptions()
    {
        $request = new Tonic\Request(array(
            'uri' => '/foo/bar'
        ));
        $this->spec($request->uri)->should->be('/foo/bar');
    }

    function itShouldHaveTheRequestUriFromRequestUriEnvironmentVar()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $request = new Tonic\Request;
        $this->spec($request->uri)->should->be('/foo/bar');
    }

    function itShouldHaveTheRequestUriFromRedirectUrlEnvironmentVar()
    {
        $_SERVER['REDIRECT_URL'] = '/myhost/web/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/myhost/web/dispatch.php';
        $request = new Tonic\Request;
        $this->spec($request->uri)->should->be('/foo/bar');
    }

    function itShouldHaveTheRequestUriAndAcceptMimetype()
    {
        $request = new Tonic\Request(array(
            'uri' => '/foo/bar.html'
        ));
        $this->spec($request->accept[0])->should->be('text/html');
    }

    function itShouldHaveTheRequestUriAndAcceptLanguage()
    {
        $request = new Tonic\Request(array(
            'uri' => '/foo/bar.fr'
        ));
        $this->spec($request->acceptLanguage[0])->should->be('fr');
        $request = new Tonic\Request(array(
            'uri' => '/foo/bar.en-gb'
        ));
        $this->spec($request->acceptLanguage[0])->should->be('en-gb');
    }

    function itShouldHaveTheRequestUriAndAcceptMimetypeAndLanguage()
    {
        $request = new Tonic\Request(array(
            'uri' => '/foo/bar.html.fr'
        ));
        $this->spec($request->accept[0])->should->be('text/html');
        $this->spec($request->acceptLanguage[0])->should->be('fr');
        $request = new Tonic\Request(array(
            'uri' => '/foo/bar.fr.html'
        ));
        $this->spec($request->accept[0])->should->be('text/html');
        $this->spec($request->acceptLanguage[0])->should->be('fr');
    }

    function itShouldHaveTheRequestMethodFromOptions()
    {
        $request = new Tonic\Request(array(
            'method' => 'POST'
        ));
        $this->spec($request->method)->should->be('POST');
    }

    function itShouldHaveTheRequestMethodFromRequestMethodHeader()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Tonic\Request;
        $this->spec($request->method)->should->be('POST');
    }

    function itShouldHaveTheRequestMethodFromMethodOverrideHeader()
    {
        $_SERVER['X_HTTP_METHOD_OVERRIDE'] = 'POST';
        $request = new Tonic\Request;
        $this->spec($request->method)->should->be('POST');
    }

    function itShouldHaveTheRequestContentType()
    {
        $request = new Tonic\Request(array(
            'contentType' => 'text/html'
        ));
        $this->spec($request->contentType)->should->be('text/html');
        
        $_SERVER['CONTENT_TYPE'] = 'text/html';
        $request = new Tonic\Request;
        $this->spec($request->contentType)->should->be('text/html');
        
        $_SERVER['CONTENT_TYPE'] = 'text/html; charset=ISO-8859-4';
        $request = new Tonic\Request;
        $this->spec($request->contentType)->should->be('text/html');
    }

    function itShouldHaveTheAcceptMimetypes()
    {
        $_SERVER['ACCEPT'] = 'text/html,application/xhtml+xml;q=0.8,application/xml;q=0.9,*/*;q=0.7';
        $request = new Tonic\Request;
        $this->spec($request->accept[0])->should->be('text/html');
        $this->spec($request->accept[2])->should->be('application/xhtml+xml');
        $this->spec($request->accept[1])->should->be('application/xml');
        $this->spec($request->accept[3])->should->be('*/*');
    }

    function itShouldHaveTheAcceptLanguages()
    {
        $_SERVER['ACCEPT_LANGUAGE'] = 'en-GB,en;q=0.8,nl;q=0.9';
        $request = new Tonic\Request;
        $this->spec($request->acceptLanguage[0])->should->be('en-gb');
        $this->spec($request->acceptLanguage[2])->should->be('en');
        $this->spec($request->acceptLanguage[1])->should->be('nl');
    }
}