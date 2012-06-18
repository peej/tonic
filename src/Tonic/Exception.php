<?php

namespace Tonic;

class Exception extends \Exception {

    function appendMessage($msg) {
        $this->message .= $msg;
    }

}