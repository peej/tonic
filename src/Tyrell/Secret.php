<?php

namespace Tyrell;

use Tonic\Resource,
    Tonic\Response,
    Tonic\UnauthorizedException;

/**
 * Simple HTTP authentication example.
 *
 * The condition annotation @secure maps to the secure() method allowing us to easily
 * secure the mySecret() method with the given username and password
 *
 * @uri /secret
 */
class Secret extends Resource {

    /**
     * Resource method to handle GET request and return the secret only if the user
     * has the credentials specified in the @secure annotation.
     *
     * @method GET
     * @secure aUser aPassword
     * @return str
     */
    function mySecret() {
        return 'My secret';
    }

    /**
     * Condition method for the @secure annotation that checks the requests HTTP
     * authentication details against the username and password given in the annotation.
     *
     * @param str $username
     * @param str $password
     * @throws UnauthorizedException
     */
    function secure($username, $password) {
        if (
            isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == $username &&
            isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == $password
        ) {
            return;
        }
        #return new Response(401, 'No entry', array('wwwAuthenticate' => 'Basic realm="My Realm"'));
        throw new UnauthorizedException('No entry');
    }
}