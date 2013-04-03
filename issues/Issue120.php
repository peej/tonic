<?php

namespace Tonic;

/**
 *
 * @uri /issue120
 * @uri /issue120/([0-9]+)
 */
class Issue120 extends Resource {

    /**
     * @method get
     */
    function get($number)
    {
        return $number;
    }

}