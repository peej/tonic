<?php

namespace Tonic;

/**
 *
 * @uri /issue107
 * @uri /issue107/:id
 */
class Issue107 extends Resource {

    /**
     * @method GET
     * @param str $id
     * @return Tonic\Response
     */
    public function get($id = 'default') {
        return "get $id";
    }

    /**
     * @method GET
     * @only self
     * @return Tonic\Response
     */
    public function getSelf() {
        return "get SELF";
    }

    /**
     * @method POST
     * @return string
     */
    public function create() {
        return "create";
    }

    public function only($allowedName) {
        if (strtolower($allowedName) != strtolower($this->id)) throw new ConditionException;
        return true;
    }
}