<?php

class WebFileTests extends TestSuite {
    function __construct() {
        parent::__construct('Example Web Files');
        $this->collect(dirname(__FILE__),
        		new SimplePatternCollector('/_webtest.php/'));
    }
}