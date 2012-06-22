<?php

/**
 * The obligitory Hello World example
 * 
 * @uri /hello
 * @uri /hello/:name
 * @priority 10
 */
class Hello extends Tonic\Resource {

    /**
     * @method GET
     * @param  str $name
     * @return Response
     */
    function sayHello($name = 'World') {
        return 'Hello '.$name;
    }

    /**
     * @method GET
     * @priority 2
     * @condition only deckard
     * @return Response
     */
    function replicants() {
        return 'Replicants are like any other machine - they\'re either a benefit or a hazard.';
    }

    /**
     * @method GET
     * @priority 2
     * @condition only roy
     * @return Response
     */
    function iveSeenThings() {
        return 'I\'ve seen things you people wouldn\'t believe.';
    }

    /**
     * Only allow specific :name parameter to access the method
     */
    function only($allowedName) {
        if (strtolower($allowedName) != strtolower($this->name)) throw new Tonic\ConditionException;
    }

    /**
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
     * @method POST
     * @accepts application/json
     * @provides application/json
     * @param  str $name
     * @return Response
     */
    function feedTheComputer() {
        return new Tonic\Response(200, json_encode(array(
            'hello' => $name
        )));
    }

}
