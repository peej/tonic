<?php

/**
 * The obligitory Hello World example
 *
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 * 
 * @uri /hello
 * @uri /hello/:name
 */
class Hello extends Tonic\Resource {

    /**
     * Use this method to handle GET HTTP requests.
     *
     * The optional :name parameter in the URL available as the first parameter to the method.
     *
     * Method can return a string response, an HTTP status code, an array of status code and
     * response body, or a full Tonic\Response object.
     *
     * @method GET
     * @param  str $name
     * @return str
     */
    function sayHello($name = 'World') {
        return 'Hello '.$name;
    }

    /**
     * The @priority annotation makes this method take priority over the above method.
     *
     * The @condition annotation requires the given method to execute without throwing an
     * exception allowing the addition of an arbitary condition to this method.
     *
     * @method GET
     * @priority 2
     * @condition only deckard
     * @return str
     */
    function replicants() {
        return 'Replicants are like any other machine - they\'re either a benefit or a hazard.';
    }

    /**
     * @method GET
     * @priority 2
     * @condition only roy
     * @return str
     */
    function iveSeenThings() {
        return 'I\'ve seen things you people wouldn\'t believe.';
    }

    /**
     * Condition method for above methods.
     *
     * Only allow specific :name parameter to access the method
     */
    function only($allowedName) {
        if (strtolower($allowedName) != strtolower($this->name)) throw new Tonic\ConditionException;
    }

    /**
     * The @provides annotation makes method only match requests that have a suitable accept
     * header or URL extension (ie: /hello.json) and causes the response to automatically
     * contain the correct content-type response header.
     *
     * @method GET
     * @provides application/json
     * @return Tonic\Response
     */
    function sayHelloComputer() {
        return new Tonic\Response(200, json_encode(array(
            'hello' => $this->name
        )));
    }

    /**
     * All HTTP methods are supported. The @accepts annotation makes method only match if the
     * request body content-type matches.
     *
     * curl -i -H "Content-Type: application/json" -X POST -d '{"hello": "computer"}' http://localhost/www/tonic/web/hello.json
     *
     * @method POST
     * @accepts application/json
     * @provides application/json
     * @return Response
     */
    function feedTheComputer() {
        return new Tonic\Response(200, $this->request->data);
    }

}