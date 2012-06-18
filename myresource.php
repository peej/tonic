<?php

/**
 * @uri /hello/:name
 * @priority 10
 * @namespace myNamespace
 */
class MyResource extends Tonic\Resource {

    /**
     * @method GET
     * @condition myCondition
     * @param  str $name
     * @return Response
     */
    function myMethod($name = NULL) {
        return array(200, 'Hello '.$name);
    }

   /**
     * @method GET
     * @condition hasQuery woo yay
     * @param  str $name
     * @return Response
     */
    function myOtherMethod($name) {
        return 'Goodbye '.$name;
    }

    function myCondition() {
        if ($this->request->hostname != 'localhost') throw new Tonic\ConditionException;
    }

    function hasQuery($name, $value) {
        if (!isset($_GET[$name]) || $_GET[$name] != $value) throw new Tonic\ConditionException;
    }

}