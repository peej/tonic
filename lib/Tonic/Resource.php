<?php

/**
 * Base resource class
 * @namespace Tonic\Lib
 */
class Tonic_Resource {
	
    var $parameters = array();
    
    /**
     * Resource constructor
     * @param string[] parameters Parameters passed in from the URL as matched from the URI regex
     */
    function __construct($parameters = array()) {
        $this->parameters = $parameters;
    }
    
    /**
     * Execute a request on this resource.
     * @param Tonic_Request $request
     * @return Tonic_Response
     */
    function exec($request) {
        if (method_exists($this, $request->method)) {
            $parameters = $this->parameters;
            array_unshift($parameters, $request);
            
            $response = call_user_func_array(
                array($this, $request->method),
                $parameters
            );
        } else {
            // send 405 method not allowed
            $response = new Tonic_Response($request);
            $response->code = Tonic_Response::METHODNOTALLOWED;
            $response->body = sprintf(
                'The HTTP method "%s" used for the request is not allowed for the resource "%s".',
                $request->method,
                $request->uri
            );
        }
        
        # good for debugging, remove this at some point
        $response->addHeader('X-Resource', get_class($this));
        
        return $response;
    }
    
}