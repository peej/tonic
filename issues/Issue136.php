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

    function callMeSuper() {
        $this->after(function ($response) {
            $response->body .= ' call me super!';
        });
    }

    /**
     * @method OTHERTHING
     */
    function override()
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

    /**
     * @callMe
     */
    function get()
    {
        return 'get';
    }

    function callMe() {
        $this->after(function ($response) {
            $response->body .= ' call me maybe?';
        });
    }

    /**
     * @callMeSuper
     */
    function post()
    {
        return 'post';
    }

    /**
     * @method PUT
     * @method WOOT
     */
    function override()
    {
        return 'override';
    }

}