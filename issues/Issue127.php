<?php

namespace Tonic;

/**
 *
 * @uri /issue127
 */
class Issue127 extends Resource {

    /**
     * @method GET
     */
    function text()
    {
        return 'text';
    }

    /**
     * @method GET
     * @provides application/json
     */
    function json()
    {
        return 'json';
    }

}