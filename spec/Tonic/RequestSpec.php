<?php

namespace spec\Tonic;

use PhpSpec\ObjectBehavior;

class RequestSpec extends ObjectBehavior
{
    function letgo()
    {
        unset($_SERVER);
        unset($_GET);
    }

    function it_should_be_initializable()
    {
        $this->shouldHaveType('Tonic\Request');
    }

    function it_should_get_the_request_uri_from_constructor_options()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->uri->shouldBe('/foo/bar');
    }

    function it_should_get_the_request_uri_from_request_url_environment_var()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $this->uri->shouldBe('/foo/bar');
    }

    function it_should_get_the_request_uri_from_redirect_url_environment_var()
    {
        $_SERVER['REDIRECT_URL'] = '/myhost/web/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/myhost/web/dispatch.php';
        $this->uri->shouldBe('/foo/bar');
    }

    function it_should_get_the_accept_mimetype_from_the_uri()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar.html'
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->accept->shouldBe(array('text/html'));
    }

    function it_should_get_the_accept_language_from_the_uri()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar.fr'
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->acceptLanguage->shouldBe(array('fr'));
    }

    function it_should_get_the_request_uri_and_accept_mimetype_and_language()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar.html.fr'
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->accept->shouldBe(array('text/html'));
        $this->acceptLanguage->shouldBe(array('fr'));
    }

    function it_should_get_the_request_uri_and_accept_mimetype_and_language_when_ordered_differently()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar.fr.html'
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->accept->shouldBe(array('text/html'));
        $this->acceptLanguage->shouldBe(array('fr'));
    }

    function it_should_get_the_request_method_from_constructor_options()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar',
            'method' => 'POST'
        ));
        $this->method->shouldBe('POST');
    }

    function it_should_get_the_request_method_from_the_request_method_header()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->method->shouldBe('POST');
    }

    function it_should_get_the_request_method_from_method_override_header()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['X_HTTP_METHOD_OVERRIDE'] = 'PUT';
        $this->method->shouldBe('PUT');
    }

    function it_should_get_the_request_method_from_method_override_header_only_if_actual_method_is_post()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['X_HTTP_METHOD_OVERRIDE'] = 'PUT';
        $this->method->shouldBe('GET');
    }

    function it_should_get_the_request_method_from_the_url()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar!DELETE',
            'uriMethodOverride' => true
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->method->shouldBe('DELETE');
    }

    function it_should_get_the_request_method_from_the_querystring()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['_method'] = 'delete';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar',
            'uriMethodOverride' => true
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->method->shouldBe('DELETE');
    }

    function it_should_get_the_request_method_from_url_only_if_actual_method_is_post()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar!DELETE',
            'uriMethodOverride' => true
        ));
        $this->uri->shouldBe('/foo/bar!DELETE');
        $this->method->shouldBe('GET');
    }

    function it_should_get_the_request_method_from_the_querystring_only_if_actual_method_is_post()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_method'] = 'delete';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar',
            'uriMethodOverride' => true
        ));
        $this->uri->shouldBe('/foo/bar');
        $this->method->shouldBe('GET');
    }

    function it_should_get_the_request_content_type_from_constructor_options()
    {
        $this->beConstructedWith(array(
            'uri' => '/foo/bar',
            'contentType' => 'text/html'
        ));
        $this->contentType->shouldBe('text/html');
    }
    
    function it_should_get_the_request_content_type_from_the_environment()
    {
        $_SERVER['CONTENT_TYPE'] = 'text/html';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->contentType->shouldBe('text/html');
    }

    function it_should_get_the_request_content_type_from_the_environment_with_charset_present()
    {
        $_SERVER['CONTENT_TYPE'] = 'text/html; charset=ISO-8859-4';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->contentType->shouldBe('text/html');
    }

    function it_should_get_the_accept_mimetypes_from_the_environment()
    {
        $_SERVER['ACCEPT'] = 'text/html,application/xhtml+xml;q=0.8,application/xml;q=0.9,*/*;q=0.7';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->accept->shouldBe(array(
            'text/html', 'application/xml', 'application/xhtml+xml', '*/*'
        ));
    }

    function it_should_get_the_accept_languages_from_the_environment()
    {
        $_SERVER['ACCEPT_LANGUAGE'] = 'en-GB,en;q=0.8,nl;q=0.9';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->acceptLanguage->shouldBe(array(
            'en-gb', 'nl', 'en'
        ));
    }

    function it_should_get_the_if_match_header_from_the_environment()
    {
        $_SERVER['IF_MATCH'] = 'quux, "xyzzy"';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->ifMatch->shouldBe(array('quux', 'xyzzy'));
    }

    function it_should_get_the_if_none_match_header_from_the_environment()
    {
        $_SERVER['IF_NONE_MATCH'] = 'quux, "xyzzy"';
        $this->beConstructedWith(array(
            'uri' => '/foo/bar'
        ));
        $this->ifNoneMatch->shouldBe(array('quux', 'xyzzy'));
    }

}
