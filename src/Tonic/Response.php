<?php

namespace Tonic;

class Response {

    public $code, $body;
    private $headers = array();

    const OK = 200,
        CREATED = 201,
        NOCONTENT = 204,
        MOVEDPERMANENTLY = 301,
        FOUND = 302,
        SEEOTHER = 303,
        NOTMODIFIED = 304,
        TEMPORARYREDIRECT = 307,
        BADREQUEST = 400,
        UNAUTHORIZED = 401, 
        FORBIDDEN = 403, 
        NOTFOUND = 404, 
        METHODNOTALLOWED = 405,
        NOTACCEPTABLE = 406,
        GONE = 410,
        LENGTHREQUIRED = 411,
        PRECONDITIONFAILED = 412,
        UNSUPPORTEDMEDIATYPE = 415,
        IMATEAPOT = 418,
        ENHANCEYOURCALM = 420,
        INTERNALSERVERERROR = 500;

    /**
     * Map of HTTP response codes
     * @var str[]
     */
    private $codes = array(
        Response::OK => 'OK',
        Response::CREATED => 'Created',
        Response::NOCONTENT => 'No Content', 
        Response::MOVEDPERMANENTLY => 'Moved Permanently',
        Response::FOUND => 'Found', 
        Response::SEEOTHER => 'See Other', 
        Response::NOTMODIFIED => 'Not Modified', 
        Response::TEMPORARYREDIRECT => 'Temporary Redirect', 
        Response::BADREQUEST => 'Bad Request', 
        Response::UNAUTHORIZED => 'Unauthorized', 
        Response::FORBIDDEN => 'Forbidden', 
        Response::NOTFOUND => 'Not Found', 
        Response::METHODNOTALLOWED => 'Method Not Allowed',
        Response::NOTACCEPTABLE => 'Not Acceptable',
        Response::GONE => 'Gone',
        Response::LENGTHREQUIRED => 'Length Required',
        Response::PRECONDITIONFAILED => 'Precondition Failed',
        Response::UNSUPPORTEDMEDIATYPE => 'Unsupported Media Type',
        Response::IMATEAPOT => 'I\'m A Teapot',
        Response::UNSUPPORTEDMEDIATYPE => 'Enhance Your Calm',
        Response::INTERNALSERVERERROR => 'Internal Server Error'
    );

    function __construct($code = 204, $body = '') {
        $this->code = $code;
        $this->body = $body;
    }

    function header($name, $value) {
        $this->headers[$name] = $value;
    }

    function output() {
        header('HTTP/1.1 '.$this->code.' '.$this->codes[$this->code]);
        foreach ($this->headers as $name => $value) {
            header($name.': '.$value);
        }
        echo $this->body;
    }

}