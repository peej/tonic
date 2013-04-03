<?php

namespace Tonic;

class NotFoundException extends Exception
{
    protected $code = 404;
    protected $message = 'The server has not found anything matching the Request-URI';
}
