<?php

namespace Tonic;

class BaseResource extends Resource {

}

/**
 *
 * @uri /issue122
 */
class Issue122 extends BaseResource {

    protected function routemethod($method)
    {
        parent::method($method);
    }

    /**
     * @routemethod get
     */
    function get()
    {
        return 'get';
    }

}