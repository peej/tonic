<?php

namespace Tonic;

class Response {

    public $code, $body;

    function __construct($code = 204, $body = '') {
        $this->code = $code;
        $this->body = $body;
    }

    function output() {
        header('HTTP/1.1 '.$this->code);
        echo $this->body;
    }

}