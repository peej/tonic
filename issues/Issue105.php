<?php

namespace Tonic;

/**
 *
 * @uri /issue105
 * @uri /issue105/:id
 */
class Issue105 extends Resource {
    private $username;

   /**
     * @method GET
     * @secure 
     * @param str $id
     * @return str
     */
    public function getResource($id = 'Papa'){
        return 'Hello '.$id;
    }

    /**
     * @method GET
     * @priority 2
     * @only self
     * @secure
     * @return str
     */
    public function getSelf() {
        return $this->getResource($this->username);
    }

    public function secure() {
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $this->username = strtolower(trim($_SERVER['PHP_AUTH_USER']));
            return;
        }
        throw new UnauthorizedException;
    }

    public function only($allowedName) {
        if (strtolower($allowedName) != strtolower($this->id)) throw new ConditionException;
    }
}