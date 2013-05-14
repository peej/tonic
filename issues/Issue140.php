<?php

namespace Tonic;

/**
 *
 * @uri /issue140
 */
class Issue140 extends Resource {

    private $thing;

    function setup()
    {
        if (
            $this->app instanceof Application &&
            $this->request instanceof Request
        ) {
            $this->thing = 'setup';
        }
    }

    /**
     * @method GET
     */
    function handle()
    {
        return $this->thing;
    }

}