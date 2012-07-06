<?php

namespace Tonic;

class UnsupportedMediaTypeException extends Exception
{
    protected $code = 415;
}
