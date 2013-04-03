<?php

namespace Tonic;

/**
 *
 * @uri /issue130/{id}
 */
class Issue130 extends Resource {

    /**
     * @method GET
     */
    function handle($id)
    {
        return $id;
    }

}