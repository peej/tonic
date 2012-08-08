<?php

namespace Tonic;

class NotAcceptableException extends Exception
{
    protected $code = 406;
    protected $message = 'The resource identified by the request is only capable of generating response entities which have content characteristics not acceptable according to the accept headers sent in the request';
}
