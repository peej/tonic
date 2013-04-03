<?php

namespace Tonic;

class Issue122Base extends Resource {

    protected function routemethod($method)
    {
        parent::method($method);
    }

}

/**
 *
 * @uri /issue122
 */
class Issue122 extends Issue122Base {

    /**
     * @routemethod get
     */
    function get()
    {
        return 'get';
    }

}