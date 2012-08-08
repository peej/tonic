<?php

namespace Tonic;

class UnsupportedMediaTypeException extends Exception
{
    protected $code = 415;
    protected $message = 'The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method';
}
