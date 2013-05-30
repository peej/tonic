<?php

namespace Tyrell;

use Tonic;

/**
 * Simple HTTP authentication example.
 *
 * The condition annotation @secure maps to the secure() method allowing us to easily
 * secure the mySecret() method with the given username and password
 *
 * @uri /secret
 */
class Secret extends Tonic\Resource {

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
        throw new Tonic\UnauthorizedException('No entry');
    }
}


/**
 * Secure an entire resource.
 *
 * The setup() method is used to do the security validation upon resource execution before
 * the correct resource method is picked for execution.
 *
 * @uri /secret2
 */
class TotallySecureResource extends Tonic\Resource {

    private $username = 'aUser2';
    private $password = 'aPassword2';

    /**
     * The setup() method is called when the resource is executed. We don't do this check
     * within the resource constructor as we can't cleanly throw an exception from within
     * an object constructor.
     */
    function setup() {
        if (
            !isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $this->username ||
            !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_PW'] != $this->password
        ) {
            throw new Tonic\UnauthorizedException;
        }
    }

    /**
     * All resource methods will be secured behind the check within the setup() method.
     *
     * @method GET
     */
    function secret() {
        return 'My secret';
    }
}