<?php

/**
 * Default resource to use, matches all URIs
 * @uri /woo 9999
 */
class DefaultResource extends Resource {
    
    function get($request) {
        
        $response = new Response($request);
        $response->code = Response::OK;
        $response->addHeader('content-type', 'text/plain');
        $response->body = $request->__toString();
        return $response;
        
    }
    
}


/**
 *  @uri /hidden 10
 */
class SpecificResource extends Resource {
    
    function get($request) {
        
        $response = new Response($request);
        $response->code = Response::OK;
        $response->body = 'Hidden somewhere';
        return $response;
        
    }
    
}

?>
