<?php

require_once __DIR__.'/../../lib/Tonic/Autoloader.php';

set_include_path(get_include_path().':'.__DIR__.'/..');

/**
 * @uri /hello/:name
 * @priority 10
 * @namespace myNamespace
 */
class MyResource extends Tonic\Resource {

    /**
     * @method GET
     * @accepts application/x-www-form-urlencoded
     * @accepts application/multipart
     * @provides text/html
     * @condition myCondition
     * @param  str $name
     * @return Response
     */
    function myMethod($name = NULL) {
        #return 200;
        #return 'Hello '.$name;
        return array(200, 'Hello '.$name);
        return new Response;
    }

    function myCondition() {
        return TRUE;
    }

}