<?php

namespace Tonic;

/**
 * Model a HTTP response
 */
class Response
{
    /**
     * HTTP response code constant
     */
    const
        HTTPCONTINUE                    = 100,
        SWITCHINGPROTOCOLS              = 101,

        OK                              = 200,
        CREATED                         = 201,
        ACCEPTED                        = 202,
        NONAUTHORATIVEINFORMATION       = 203,
        NOCONTENT                       = 204,
        RESETCONTENT                    = 205,
        PARTIALCONTENT                  = 206,

        MULTIPLECHOICES                 = 300,
        MOVEDPERMANENTLY                = 301,
        FOUND                           = 302,
        SEEOTHER                        = 303,
        NOTMODIFIED                     = 304,
        USEPROXY                        = 305,
        TEMPORARYREDIRECT               = 307,

        BADREQUEST                      = 400,
        UNAUTHORIZED                    = 401,
        PAYMENTREQUIRED                 = 402,
        FORBIDDEN                       = 403,
        NOTFOUND                        = 404,
        METHODNOTALLOWED                = 405,
        NOTACCEPTABLE                   = 406,
        PROXYAUTHENTICATIONREQUIRED     = 407,
        REQUESTTIMEOUT                  = 408,
        CONFLICT                        = 409,
        GONE                            = 410,
        LENGTHREQUIRED                  = 411,
        PRECONDITIONFAILED              = 412,
        REQUESTENTITYTOOLARGE           = 413,
        REQUESTURITOOLONG               = 414,
        UNSUPPORTEDMEDIATYPE            = 415,
        REQUESTEDRANGENOTSATISFIABLE    = 416,
        EXPECTATIONFAILED               = 417,
        IMATEAPOT                       = 418, // RFC2324

        INTERNALSERVERERROR             = 500,
        NOTIMPLEMENTED                  = 501,
        BADGATEWAY                      = 502,
        SERVICEUNAVAILABLE              = 503,
        GATEWAYTIMEOUT                  = 504,
        HTTPVERSIONNOTSUPPORTED         = 505;

    /**
     * Map of HTTP response codes
     * RFC 2616, 2324
     */
    protected $codes = array(
        Response::HTTPCONTINUE                    => 'Continue',
        Response::SWITCHINGPROTOCOLS              => 'Switching Protocols',

        Response::OK                              => 'OK',
        Response::CREATED                         => 'Created',
        Response::ACCEPTED                        => 'Accepted',
        Response::NONAUTHORATIVEINFORMATION       => 'Non-Authoritative Information',
        Response::NOCONTENT                       => 'No Content',
        Response::RESETCONTENT                    => 'Reset Content',
        Response::PARTIALCONTENT                  => 'Partial Content',

        Response::MULTIPLECHOICES                 => 'Multiple Choices',
        Response::MOVEDPERMANENTLY                => 'Moved Permanently',
        Response::FOUND                           => 'Found',
        Response::SEEOTHER                        => 'See Other',
        Response::NOTMODIFIED                     => 'Not Modified',
        Response::USEPROXY                        => 'Use Proxy',
        Response::TEMPORARYREDIRECT               => 'Temporary Redirect',

        Response::BADREQUEST                      => 'Bad Request',
        Response::UNAUTHORIZED                    => 'Unauthorized',
        Response::PAYMENTREQUIRED                 => 'Payment Required',
        Response::FORBIDDEN                       => 'Forbidden',
        Response::NOTFOUND                        => 'Not Found',
        Response::METHODNOTALLOWED                => 'Method Not Allowed',
        Response::NOTACCEPTABLE                   => 'Not Acceptable',
        Response::PROXYAUTHENTICATIONREQUIRED     => 'Proxy Authentication Required',
        Response::REQUESTTIMEOUT                  => 'Request Timeout',
        Response::CONFLICT                        => 'Conflict',
        Response::GONE                            => 'Gone',
        Response::LENGTHREQUIRED                  => 'Length Required',
        Response::PRECONDITIONFAILED              => 'Precondition Failed',
        Response::REQUESTENTITYTOOLARGE           => 'Request Entity Too Large',
        Response::REQUESTURITOOLONG               => 'Request-URI Too Long',
        Response::UNSUPPORTEDMEDIATYPE            => 'Unsupported Media Type',
        Response::REQUESTEDRANGENOTSATISFIABLE    => 'Requested Range Not Satisfiable',
        Response::EXPECTATIONFAILED               => 'Expectation Failed',
        Response::IMATEAPOT                       => 'I\'m a teapot',

        Response::INTERNALSERVERERROR             => 'Internal Server Error',
        Response::NOTIMPLEMENTED                  => 'Not Implemented',
        Response::BADGATEWAY                      => 'Bad Gateway',
        Response::SERVICEUNAVAILABLE              => 'Service Unavailable',
        Response::GATEWAYTIMEOUT                  => 'Gateway Timeout',
        Response::HTTPVERSIONNOTSUPPORTED         => 'HTTP Version Not Supported',
    );

    public
        $code = self::NOCONTENT,
        $body;

    protected
        $headers = array('content-type' => 'text/html');

    public function __construct($code = null, $body = null)
    {
        $code and $this->code = $code;
        $body and $this->body = $body;
    }

    /**
     * Get a HTTP response header
     * @param  str $name Header name, hyphens should be converted to camelcase
     * @return str
     */
    public function __get($name)
    {
        $name = strtolower(preg_replace('/([A-Z])/', '-$1', $name));

        return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
    }

    /**
     * Set a HTTP response header
     * @param str $name  Header name, hyphens should be converted to camelcase
     * @param str $value Header content
     */
    public function __set($name, $value)
    {
        $this->headers[strtolower(preg_replace('/([A-Z])/', '-$1', $name))] = $value;
    }

    /**
     * Get the HTTP response message of this response
     * @return str
     */
    protected function responseMessage()
    {
        return isset($this->codes[$this->code]) ? $this->codes[$this->code] : '';
    }

    protected function responseCode() {
        return $this->code;
    }

    /**
     * Output the response
     */
    public function output()
    {
        header('HTTP/1.1 '.$this->responseCode().' '.$this->responseMessage());
        foreach ($this->headers as $name => $value) {
            header($name.': '.$value);
        }
        echo $this->body;
    }

    public function __toString()
    {
        $code = $this->code.' '.$this->responseMessage();
        $headers = array();
        foreach ($this->headers as $name => $value) {
            $headers[]  = $name.': '.$value;
        }
        $headers = join("\n\t", $headers);

        return <<<EOF
==============
Tonic\Response
==============
Code: $code
Headers:
\t$headers
Body: $this->body

EOF;
    }

}
