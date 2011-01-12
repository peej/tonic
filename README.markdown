
PHP library/framework for building Web apps while respecting the 5 principles
of RESTful design.

 * Give every "thing" an ID (aka URIs)
 * Link things together (HATEOAS)
 * Use standard methods (aka the standard interface)
 * Resources with multiple representations (aka standard document formats)
 * Communicate statelessly

[See the Tonic site for more info](http://peej.github.com/tonic/).


How it works
============

Everything is a resource, and a resource is defined as a PHP class. An annotation
wires a URI (or a collection of URIs) to the resource, and methods that match
the HTTP methods by name allow interaction with it.

    /**
     * This class defines an example resource that is wired into the URI /example
     * @uri /example
     */
    class ExampleResource extends Resource { }

The incoming HTTP request is turned into a list of negotiated URIs based on the
accept request headers which can then be used to pick the best representation
for the response.

    /**
     * This class defines an example resource that is wired into the URI /example
     * @uri /example
     */
    class ExampleResource extends Resource {
        
        function get($request) {
            
            $response = new Response($request);
            
            $response->code = Response::OK;
            $response->body = 'Example response';
            
            return $response;
            
        }
      
    }


How to get started
==================

The best place to get started is to get the hello world example running on your
system, to do this you will need a web server running PHP4+.

Place all of the Tonic files into your PHP include path so that other scripts can
find it. By default on Windows this will probably be in "c:\php\includes\tonic" or
on Linux/Unix it will be "/usr/share/php/tonic"

Copy "docroot/dispatch.php" into your servers document root and edit it so that the
require_once statement paths point to the Tonic library and the examples.

Finally you need to route all incoming requests to dispatch.php. How you do this
depends on your web server. If you are using Apache, the simplest way is to copy
the .htaccess file from "docroot/.htaccess" into your Apache document root.


Features
========


Request URI
-----------

The URI that is processed for the request when you create the Tonic Request object
is gather by default from the REQUEST_URI Apache variable. If you need to gather
the URI from another $_SERVER variable or somewhere else then you can pass it into
the Request objects constructor as a configuration option:

    $request = new Request(array(
        'uri' => $_SERVER['PATH_INFO']
    ));


Base URI
--------

If you want to put your Tonic dispatcher at a URL that isn't the root of a domain
then you'll need to let the Request object know so that the @uri annotations ignore
it:

    $request = new Request(array(
        'baseUri' => '/some/base/uri'
    ));

Don't put a trailing slash on the end.


URI annotations
---------------

Resources are attached to their URL by their @uri annotation. As well as a straight
forward URI string, you can also use a regular expression so that a resource is
tied to a range of URIs:

    /**
     * @uri /example/([a-z]+)
     */
    class ExampleResource extends Resource { }
    
Parameters can also be specified in a similar fashion to Rails routes. Any tokens
parsed from the @uri are used to name the parameters passed to the Tonic_Request:

    /**
     * @uri /example/:parameter
     */
    class ExampleResource extends Resource { }
    
Uri specifications can be arbitarily complex. However, if parameters and regex are 
used together you will lose the naming capability and the variables will be passed
in a standard number indexed array to the Tonic_Request

    /**
     * @uri /example/:parameter/action/:actionId/([a-z]+)
     */
    class ExampleResource extends Resource { }

It is also possible for multiple resource to match the same URI, so you can
prioritise which resource should be used by specifying a priority level as part
of the annotation:

    /**
     * @uri /example/([a-z]+)
     */
    class ExampleResource extends Resource { }

    /**
     * @uri /example/apple 2
     */
    class ExampleResource extends Resource { }

By postfixing the @uri annotation with a number, of all the matching resources,
the one with the highest postfixed number will be used.


Mimetypes
---------

To handle content negotiation via filename style extensions to URLs as well the
HTTP Accept header, a mapping between extensions and mimetypes can be provided.
By default this list contains a number of common mappings, if you need to add one
or more of your own, pass them into the constructor as an array:

    $request = new Request(array(
        'mimetypes' => array(
            'ogv' => 'video/ogg'
        )
    ));


Mount points
------------

To make resources more portable, it is possible to "mount" them into your URL-space
by providing a namespace name to URL-space mapping. Every resource within that
namespace will in effect have the URL-space prefixed to their @uri annotation.

    $request = new Request(array(
        'mount' => array(
            'namespaceName' => '/some/mounted/uri'
        )
    ));

Again, don't put a trailing slash on the end.



For more information, read the code. Start with the dispatcher "docroot/dispatch.php"
and then the examples in the "examples" directory.
