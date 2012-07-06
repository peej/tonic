<?php

namespace Tonic;

class MethodNotAllowedException extends Exception
{
    protected $code = 405;
}
