<?php

namespace ANameSpace;

/**
 * @uri /woo
 * @priority 10
 */
class anotherResource extends \Tonic\Resource {

    /**
     * @method GET
     * @method POST
     * @return [type] [description]
     */
    function something() {
        return '<form method="post"><input type="submit"></form>';
    }
}
