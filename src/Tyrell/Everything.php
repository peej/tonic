<?php

namespace Tyrell;

use Tonic\Resource;

/**
 * Example of how to handle all URLs with a single resource.
 * This is useful if you want to proxy another application through Tonic in a way which can't
 * be handled via your Web server.
 *
 * @uri /everything
 * @uri /everything/(.*)
 */
class Everything extends Resource
{
    /**
     * Request/response method to handle every GET request
     * @method GET
     */
    public function doEverything($url = '')
    {
        return '/'.$url;
    }

}