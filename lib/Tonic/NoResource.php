<?php

namespace Tonic;

/**
 * 404 resource class
 */
class NoResource extends Resource {
    
    /**
     * Always return a 404 response.
     * @param Tonic\Request $request
     * @return Tonic\Response
     */
    function exec($request) {
        // send 404 not found
        $response = new Response($request);
        $response->code = Response::NOTFOUND;
        $response->body = sprintf(
            'Nothing was found for the resource "%s".',
            $request->uri
        );
        return $response;
    }
    
}
