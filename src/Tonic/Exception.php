<?php

namespace Tonic;

/**
 * Base exception class for Tonic exceptions
 */
class Exception extends \Exception
{
    protected $message  = 'An unknown Tonic exception occurred';
    protected $code     = 500;
}
