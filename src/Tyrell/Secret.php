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
     * @method GET
     * @secure aUser aPassword
     */
    function mySecret() {
        return 'My secret';
    }

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