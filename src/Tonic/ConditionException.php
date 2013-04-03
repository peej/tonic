<?php

namespace Tonic;

/**
 * Exception to be thrown when a resource method condition fails
 */
class ConditionException extends Exception
{
    protected $message = 'A method condition failed';
}
