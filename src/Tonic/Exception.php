<?php

namespace Tonic;

/**
 * Base exception class for Tonic exceptions
 */
class Exception extends \Exception
{
    /**
     * Append a message onto the existing exception message
     *
     * @param str $msg
     */
    public function appendMessage($msg)
    {
        $this->message .= $msg;
    }

}
