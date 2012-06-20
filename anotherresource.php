<?php

namespace ANameSpace;

/**
 * @uri /woo
 */
class anotherResource extends \Tonic\Resource {

    /**
     * @method GET POST
     * @return str
     */
    function something() {
        return $this->request->method.'<form method="post"><input type="submit"></form>';
    }
}
