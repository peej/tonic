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

Everything is a resource, and a resource is defined as a PHP class. Annotations
wire a URI to the resource and HTTP methods to class methods.

    /**
     * This class defines an example resource that is wired into the URI /example
     * @uri /example
     */
    class ExampleResource extends Tonic\Resource {
        
        /**
         * @method GET
         */
        function exampleMethod() {
            return new Response(Response::OK, 'Example response');
        }
      
    }

The class method can do any logic it then requires and return a Response object,
an array of status code and response body, or just a response body string.


How to get started
==================

The best place to get started is to get the hello world example running on your
system, to do this you will need a web server running PHP5.3+.

To bootstrap Tonic, include the src/Tonic/Autoloader.php file and create an instance
Tonic\Request. After you have defined your resource classes, load the matching
resource, execute it, and output the response.

    require_once '../src/Tonic/Autoloader.php';

    $request = new Tonic\Request();

    require_once '../resources/example.php';

    $resource = $request->loadResource();
    $response = $resource->exec();
    $response->output();

Finally you need to route all incoming requests to this script. Have a look in the
web directory for an example to get you going.


Features
========


URI annotations
---------------

Resources are attached to their URL by their @uri annotation:

    /**
     * @uri /example
     */
    class ExampleResource extends Tonic\Resource { }

As well as a straight forward URI string, you can also use a regular expression
so that a resource is tied to a range of URIs:

    /**
     * @uri /example/([a-z]+)
     */
    class ExampleResource extends Tonic\Resource {

        /**
         * @method GET
         */
        function exampleMethod($parameter) {
            ...
        }
    }

URL template and Rails route style @uri annotations are also supported:

    /**
     * @uri /users/{username}
     */
    class ExampleResource extends Tonic\Resource {

        /**
         * @method GET
         */
        function exampleMethod($username) {
            ...
        }
    }
    
    /**
     * @uri /users/:username
     */
    class ExampleResource extends Tonic\Resource {

        /**
         * @method GET
         */
        function exampleMethod($username) {
            ...
        }
    }

It is also possible for multiple resource to match the same URI, so you can
prioritise which resource should be used by specifying a priority level as part
of the annotation:

    /**
     * @uri /example/([a-z]+)
     */
    class ExampleResource extends Tonic\Resource { }

    /**
     * @uri /example/apple
     * @priority 2
     */
    class AnotherExampleResource extends Tonic\Resource { }

By using the @priority annotation with a number, of all the matching resources,
the one with the highest postfixed number will be used.


Mount points
------------

To make resources more portable, it is possible to "mount" them into your URL-space
by providing a namespace name to URL-space mapping. Every resource within that
namespace will in effect have the URL-space prefixed to their @uri annotation.

    $request->mount('namespaceName', '/some/mounted/uri');


Resource annotation cache
-------------------------

Parsing of resource annotations has a performance penalty. To remove this penalty and
to remove the requirement to load all resource classes up front (and to allow opcode
caching), a cache can be used to store the resource annotation data.

Passing a cache object into the Request object at construction will cause that cache to
be used to read and store the resource annotation metadata rather than read it from the
source code tokens. Tonic comes with a single cache class that stores the cache on disk.

Then rather than including your resource class files explicitly, the Request object
will load them for you if you pass in the "load" option if it can't load the metadata
from the cache.

    $request = new Tonic\Request(array(
        'load' => '../resources/*.php', // look for resource classes in here
        'cache' => new Tonic\MetadataCache('/tmp/tonic.cache') // use the metadata cache
    ));



Response exceptions
-------------------

The Request object and Resource objects can throw Tonic\Exceptions when a problem
occurs that the object does not want to handle and so relinquishes control back
to the dispatcher.

If you don't want to handle a problem within your Resource class, you can throw your
own Tonic\Exception and handle it in the dispatcher. Look at the auth example for
an example of how.




For more information, read the code. Start with the dispatcher "web/dispatch.php"
and then the examples in the "resources" directory.
