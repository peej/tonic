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


Installation
------------

The easiest way to install Tonic is via [Composer](http://getcomposer.org), if you
are not using or familiar with Composer I recommend you go read up on it.

Add Tonic to your composer.json file and run composer install/update:

    #composer.json
    {
        "require": {
            "peej/tonic": "3.*"
        }
    }

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

Alternatively you can download Tonic from Github and manually place it within your
project.


Bootstrapping
-------------

To bootstrap Tonic, use the provided web/dispatch.php script and configure your Web
server to push all requests to it via the provided .htaccess file.

For development purposes you can use PHP's built in Web server by running the following
command:

    $ php -S 127.0.0.1:8080 vendor/bin/dispatch.php

or:

    $ php -S 127.0.0.1:8080 web/dispatch.php

Once you need more, you can write your own dispatcher with your own custom behaviour.

The basic premise is to create an instance of Tonic\Application and pass it's
getResource() method a Tonic\Request instance. Then an incoming request will match
and load one of your resource classes, execute it, and output the response.

A very basic minimal dispatcher looks something like this:

    require_once '../vendor/autoload.php';

    $app = new Tonic\Application(array(
        'load' => 'example.php'
    ));
    $request = new Tonic\Request();

    $resource = $app->getResource($request);
    $response = $resource->exec();
    $response->output();


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

It is also possible for multiple resource to match the same URI or to have more than
one URI for the same resource:

    /**
     * @uri /example
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


Request object
--------------

Resource methods have access to the incoming HTTP request via the Request object.

The Request object exposes all elements of the request as public properties, including
the HTTP method, request data and content type.

Request headers are accessable via public properties named afer a camelcasing of the
headers name.

    /**
     * @uri /example
     */
    class ExampleResource extends Tonic\Resource {

        /**
         * @method GET
         */
        function exampleMethod() {
            echo $this->request->userAgent;
        }
    }



Mount points
------------

To make resources more portable, it is possible to "mount" them into your URL-space
by providing a namespace name to URL-space mapping. Every resource within that
namespace will in effect have the URL-space prefixed to their @uri annotation.

    $app = new Tonic\Application(array(
        'mount' => array('myBlog' => '/blog')
    ));


Resource annotation cache
-------------------------

Parsing of resource annotations has a performance penalty. To remove this penalty and
to remove the requirement to load all resource classes up front (and to allow opcode
caching), a cache can be used to store the resource annotation data.

Passing a cache object into the Application object at construction will cause that cache to
be used to read and store the resource annotation metadata rather than read it from the
source code tokens. Tonic comes with a single cache class that stores the cache on disk.

Then rather than including your resource class files explicitly, the Application object
will load them for you if you pass in the "load" option if it can't load the metadata
from the cache.

    $app = new Tonic\Application(array(
        'load' => '../resources/*.php', // look for resource classes in here
        'cache' => new Tonic\MetadataCache('/tmp/tonic.cache') // use the metadata cache
    ));


Method conditions
-----------------

Conditions can be added to methods via custom annotations that map to another class
method. The resource method will only match if all the conditions return without throwing
a Tonic exception.

    /**
     * @method GET
     * @hascookie foo
     */
    function exampleMethod() {
        ...
    }

    function hasCookie($cookieName) {
        if (!isset($_COOKIE[$cookieName])) throw new Tonic\ConditionException;
    }

There are a number of built in conditions provided by the base resource class.

    @priority number    Higher priority method takes precident over other matches
    @accepts mimetype   Given mimetype must match request content type
    @provides mimetype  Given mimetype must be in request accept array
    @lang language      Given language must be in request accept lang array
    @cache seconds      Send cache header for the given number of seconds

You can also add code to a condition to be executed before and after the resource method.
For example you might want to JSON decode the request input and JSON encode the response
output of your resource method in a reusable way:

    /**
     * @method GET
     * @json
     */
    function exampleMethod() {
        ...
    }

    function json() {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";
            $response->body = json_encode($response->body);
        });
    }


Response exceptions
-------------------

The Request object and Resource objects can throw Tonic\Exceptions when a problem
occurs that the object does not want to handle and so relinquishes control back
to the dispatcher.

If you don't want to handle a problem within your Resource class, you can throw your
own Tonic\Exception and handle it in the dispatcher. Look at the auth example for
an example of how.



Contributing
============

1. Fork the code on Github.

2. Install the dev dependencies via Composer using the --dev option (or install PHPSpec
and Behat on your system yourself).

    php composer.phar --dev install

3. Write a spec and then hack the code to make it pass.

4. Create a pull request.

Don't fancy hacking the code? Then [report your problem in the Github issue
tracker](https://github.com/peej/tonic/issues).

For more information, read the code. Start with the dispatcher "web/dispatch.php"
and the Hello world in the "src/Tyrell" directory.



Cookbook
========


Dependency injection container
------------------------------

You probably want a way to handle your project dependencies. Being a lightweight
HTTP framework, Tonic won't handle this for you, but does make it easy to bolt in
your own dependency injection container (ie. Pimple http://pimple.sensiolabs.org/).

For example, to construct a Pimple container and make it available to the loaded
resource, adjust your dispatcher.php as such:

    require_once '../src/Tonic/Autoloader.php';
    require_once '/path/to/Pimple.php';
    
    $app = new Tonic\Application();

    // set up the container
    $app->container = new Pimple();
    $app->container['dsn'] = 'mysql://user:pass@localhost/my_db';
    $app->container['database'] = function ($c) {
        return new DB($c['dsn']);
    };
    $app->container['dataStore'] = function ($c) {
        return new DataStore($c['database']);
    };

    $request = new Tonic\Request();
    $resource = $app->getResource($request);

    $response = $resource->exec();
    $response->output();


Input processing
----------------

Although Tonic makes available the raw input data from the HTTP request, it does
not attempt to interpret this data. If, for example, you want to process all incoming
JSON data into an array, you can do the following:

    require_once '../src/Tonic/Autoloader.php';

    $app = new Tonic\Application();
    $request = new Tonic\Request();

    // decode JSON data received from HTTP request
    if ($request->contentType == 'application/json') {
        $request->data = json_decode($request->data);
    }

    $resource = $app->getResource($request);

    $response = $resource->exec();
    $response->output();

We can also automatically encode the response in the same way:

    $response = $resource->exec();

    // encode output
    if ($response->contentType == 'application/json') {
        $response->body = json_encode($response->body);
    }

    $response->output();


RESTful modelling
-----------------

REST systems are made up of individual resources and collection resources which contain
individual resources. Here is an example of an implemention of an "object" collection
resource and an "object" resource to store within it:

    /**
     * @uri /objects
     */
    class ObjectCollection extends Tonic\Resource {

        /**
         * @method GET
         * @provides application/json
         */
        function list() {
            $ds = $this->container['dataStore'];
            return json_encode($ds->fetchAll());
        }

        /**
         * @method POST
         * @accepts application/json
         */
        function add() {
            $ds = $this->container['dataStore'];
            $data = json_decode($this->request->data);
            $ds->add($data);
            return new Tonic\Response(Tonic\Response::CREATED);
        }
    }

    /**
     * @uri /objects/:id
     */
    class Object extends Tonic\Resource {

        /**
         * @method GET
         * @provides application/json
         */
        function display() {
            $ds = $this->container['dataStore'];
            return json_encode($ds->fetch($this->id));
        }

        /**
         * @method PUT
         * @accepts application/json
         * @provides application/json
         */
        function update() {
            $ds = $this->container['dataStore'];
            $data = json_decode($this->request->data);
            $ds->update($this->id, $data);
            return $this->display();
        }

        /**
         * @method DELETE
         */
        function remove() {
            $ds = $this->container['dataStore'];
            $ds->delete($this->id);
            return new Tonic\Response(Tonic\Response::NOCONTENT);
        }
    }


Handling errors
---------------

When an error occurs, Tonic throws an exception that extends the Tonic\Exception class. You
can amend the front controller to catch these exceptions and handle them.

    $app = new Tonic\Application();
    $request = new Tonic\Request();
    try {
        $resource = $app->getResource($request);
    } catch(Tonic\NotFoundException $e) {
        $resource = new NotFoundResource($app, $request);
    }
    try {
        $response = $resource->exec();
    } catch(Tonic\Exception $e) {
        $resource = new FatalErrorResource($app, $request);
        $response = $resource->exec();
    }
    $response->output();


User authentication
-------------------

Need to secure a resource? Something like the following is a good pattern.

    /**
     * @uri /secret
     */
    class SecureResource extends Tonic\Resource {

        /**
         * @method GET
         * @secure aUser aPassword
         */
        function secret() {
            return 'My secret';
        }

        function secure($username, $password) {
            if (
                isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == $username &&
                isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == $password
            ) {
                return;
            }
            throw new Tonic\UnauthorizedException;
        }
    }

    $app = new Tonic\Application();
    $request = new Tonic\Request();
    $resource = $app->getResource($request);
    try {
        $response = $resource->exec();
    } catch(Tonic\UnauthorizedException $e) {
        $response = new Tonic\Response(401);
        $response->wwwAuthenticate = 'Basic realm="My Realm"';
    }
    $response->output();

If you want to secure a whole collection of resources and don't want to annotate them
all, you can add the annotation to a parent class and it will be inherited to overridden
child methods, or you can add the security logic to the resource's constructor so that
all of its request methods are secured regardless of annotations.

    /**
     * @uri /secret
     */
    class SecureResource extends Tonic\Resource {

        private $username = 'aUser';
        private $password = 'aPassword';

        function setup() {
            if (
                !isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $this->username ||
                !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_PW'] != $this->password
            ) {
                throw new Tonic\UnauthorizedException;
            }
        }

        /**
         * @method GET
         */
        function secret() {
            return 'My secret';
        }
    }


Response templating
-------------------

The use of a templating engine for generation of output is a popular way to separate
views from application logic. You can easily create a method condition that adds an
after filter to pass the response through a templating engine like Smarty or Twig.

    /**
     * @uri /templated
     */
    class Templated extends Tonic\Resource {

        /**
         * @method GET
         * @template myView.html
         */
        function pretty() {
            return new Tonic\Response(200, array(
                'title' => 'All you pretty things',
                'foo' => 'bar'
            ));
        }

        function template($templateName) {
            $this->after(function ($response) use ($templateName) {
                $smarty = $this->app->smarty;
                if (is_array($response->body)) {
                    $smarty->assign($response->body);
                }
                $response->body = $smarty->fetch($templateName);
            });
        }
    }

    $app = new Tonic\Application();
    $app->smarty = new Smarty\Smarty();
    $request = new Tonic\Request();
    $resource = $app->getResource($request);
    $response = $resource->exec();
    $response->output();


Full example
------------

For a full project example, checkout the "example" branch which is an orphaned branch
containing a Tonic project that exposes a MySQL database table.
