<?php

namespace Tyrell;

use Tonic\Resource,
    Tonic\Response,
    Tonic\UnauthorizedException;

/**
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
        throw new UnauthorizedException;
    }
}