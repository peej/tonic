<?php

/**
 * The Hello World of Tonic
 *
 * This example outputs a simple hello world message and the details of the
 * request and response as generated by Tonic. It also demonstrates etags and
 * "if none match" functionality.
 *
 * @namespace Tonic\Examples\Helloworld
 * @uri /helloworld
 */
class HelloWorldResource extends Tonic_Resource {
    
    /**
     * Handle a GET request for this resource
     * @param Tonic_Request request
     * @return Tonic_Response
     */
    function get($request) {
        
        $response = new Tonic_Response($request);
        
        $etag = md5($request->uri);
        if ($request->ifNoneMatch($etag)) {
            
            $response->code = Tonic_Response::NOTMODIFIED;
            
        } else {
            
            $response->code = Tonic_Response::OK;
            $response->addHeader('Content-type', 'text/plain');
            $response->addEtag($etag);
            $response->body =
                "Hello world!\n".
                "\n".
                "This request:\n".
                "\n".
                $request->__toString()."\n".
                "\n".
                "This response:\n".
                "\n".
                $response->__toString();
            
        }
        
        return $response;
        
    }
    
}

?>
