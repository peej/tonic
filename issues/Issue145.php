<?php

namespace Tonic;

/**
 * @uri /issue145
 */
class Issue145 extends Resource {

    /**
     * @method GET
     * @secure
     * @return Tonic\Response
     */
    public function getThing() {
        return 'thing';
    }

    /**
     * @method PUT
     * @secure
     * @return Tonic\Response
     */
    public function updateThing() {
        return 'updated thing';
    }

    public function secure() {
        throw new UnauthorizedException('You are never welcome!');
    }

}