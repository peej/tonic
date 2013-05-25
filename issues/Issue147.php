<?php

namespace Tonic;

class Issue147Foundation extends Resource {

    protected $thing = 0;
    protected $otherthing = null;

    protected function thing() {
        $this->thing++;
    }

    protected function otherthing($val) {
        $this->otherthing = $val;
    }

     /**
     * @method GET
     * @thing
     * @otherthing foo
     */
    public function tother() {
        return (string)$this->thing.$this->otherthing;
    }

}

class Issue147Base extends Issue147Foundation {

     /**
     * @thing
     * @otherthing bar
     */
    public function tother() {
        return parent::tother();
    }

}

/**
 * @uri /issue147
 */
class Issue147 extends Issue147Base {

}