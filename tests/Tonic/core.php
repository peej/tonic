<?php

class CoreFileTests extends TestSuite {
    function __construct() {
        parent::__construct('Core Tonic Files');
        $this->collect(dirname(__FILE__),
        		new SimplePatternCollector('/_test.php/'));
    }
}