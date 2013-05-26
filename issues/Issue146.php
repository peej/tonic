<?php

namespace Tonic;

/**
 *
 * @uri /issue146
 */
class Issue146 extends Resource {

    /**
     * @method GET
     */
    function handle()
    {
        return $this->request->xAuthentication;
    }

}