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

    public
        $code = self::NOCONTENT,
        $body = null,
        $headers = array('content-type' => 'text/html');

    public function __construct($code = null, $body = null, $headers = array())
    {
        if (is_int($code)) {
            $this->code = $code;
            $this->body = $body;
        } elseif ($code && !$body) {
            $this->code = self::OK;
            $this->body = $code;
        }
        if (is_array($headers)) {
            foreach ($headers as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Factory method to create a Response from a variety of inputs
     *
     * @param mixed $response
     */
    public static function create($response)
    {
        if (is_array($response)) {
            return new Response($response[0], $response[1]);
        } elseif (is_int($response)) {
            return new Response($response);
        } elseif (is_string($response)) {
            return new Response(200, $response);
        } elseif ($response instanceof Response) {
            return $response;
        }
        return new Response;
    }

    private function getHeaderName($name) {
        return strtolower($name[0].preg_replace('/([A-Z])/', '-$1', substr($name, 1)));
    }

    /**
     * Get a HTTP response header
     * @param  str $name Header name, hyphens should be converted to camelcase
     * @return str
     */
    public function __get($name)
    {
        $name = $this->getHeaderName($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
    }

    /**
     * Set a HTTP response header
     * @param str $name  Header name, hyphens should be converted to camelcase
     * @param str $value Header content
     */
    public function __set($name, $value)
    {
        $this->headers[strtolower(preg_replace('/([A-Z])/', '-$1', $this->getHeaderName($name)))] = $value;
    }

    /**
     * Get the HTTP response code of this response
     * @return int
     */
    protected function responseCode() {
        return $this->code;
    }

    /**
     * Output the response
     */
    public function output()
    {
        foreach ($this->headers as $name => $value) {
            header($name.': '.$value, true, $this->responseCode());
        }
        echo $this->body;
    }

    public function __toString()
    {
        $headers = array();
        foreach ($this->headers as $name => $value) {
            $headers[]  = $name.': '.$value;
        }
        $headers = join("\n\t", $headers);

        return <<<EOF
==============
Tonic\Response
==============
Code: $this->code
Headers:
\t$headers
Body: $this->body

EOF;
    }

}
