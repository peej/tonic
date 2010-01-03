<?php

/**
 * Example resource
 * @package Tonic/Resources
 * @uri /example
 */
class ExampleResource extends Resource {
    
    /**
     * Handle a GET request for this resource
     * @param Request request
     * @return Response
     */
    function get($request) {
        
        $response = new Response($request);
        
        $etag = md5($request->uri);
        if ($request->ifNoneMatch($etag)) {
            
            $response->code = Response::NOTMODIFIED;
            
        } else {
            
            $response->code = Response::OK;
            $response->addHeader('content-type', 'text/plain');
            $response->addEtag($etag);
            $response->body = $request->__toString();
            
        }
        
        return $response;
        
    }
    
}

?>
