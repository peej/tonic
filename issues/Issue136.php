<?php

namespace Tonic;

class Issue136Base extends Resource {

    /**
     * @method GET
     */
    function get()
    {
        return 'base';
    }

}

interface Issue136Interface {

    /**
     * @method POST
     */
    function post();
}

/**
  * @uri /issue136
 */
class Issue136 extends Issue136Base implements Issue136Interface {

    function get()
    {
        return 'get';
    }

    function post()
    {
        return 'post';
    }

}