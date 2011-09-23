<?php

/**
 * Authentication example
 *
 * An example authentication resource, the isSecured() method can be used to ensure
 * that only authorised users can access the resource.
 *
 * username: user
 * password: pass
 *
 * @namespace Tonic\Examples\Auth
 * @uri /auth
 */
class AuthResource extends Resource {
    
    const USERNAME = 'user';
    const PASSWORD = 'pass';
    
    function isSecured() {
        
        if (
            isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == AuthResource::USERNAME &&
            isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == AuthResource::PASSWORD
        ) {
            return;
        }
        
        throw new ResponseException('Incorrect username and password', Response::UNAUTHORIZED);
        
    }
    
    /**
     * Handle a GET request for this resource
     * @param Request request
     * @return Response
     */
    function get($request) {
        
        $this->isSecured();
        
        $response = new Response($request);
        
        $response->body = 'You have access to the secret';
        
        return $response;
        
    }
    
}

