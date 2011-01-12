<?php

/**
 * 404 resource class
 * @namespace Tonic\Lib
 */
class Tonic_NoResource extends Tonic_Resource {
    
    /**
     * Always return a 404 response.
     * @param Tonic_Request $request
     * @return Tonic_Response
     */
    function exec($request) {
        // send 404 not found
        $response = new Tonic_Response($request);
        $response->code = Tonic_Response::NOTFOUND;
        $response->body = sprintf(
            'Nothing was found for the resource "%s".',
            $request->uri
        );
        return $response;
    }
    
}
