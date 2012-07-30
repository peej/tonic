<?php

namespace Tonic;

class UnauthorizedException extends Exception
{
    protected $code = 401;
    protected $message = 'The request requires user authentication';
}
