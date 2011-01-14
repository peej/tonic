<?php

namespace Tonic;

/**
 * Model the data of the outgoing HTTP response
 */
class Response {
    
    /**
     * HTTP response code constant
     */
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
          INTERNALSERVERERROR = 500;
    
    /**
     * The request object generating this response
     * @var Tonic_Request
     */
    var $request;
    
    /**
     * The HTTP response code to send
     * @var int
     */
    var $code = Response::OK;
    
    /**
     * The HTTP headers to send
     * @var string[]
     */
    var $headers = array();
    
    /**
     * The HTTP response body to send
     * @var string
     */
    var $body;
    
    /**
     * Create a response object.
     * @param Tonic\Request $request The request object generating this response
     * @param string $uri The URL of the actual resource being used to build the response
     */
    function __construct($request, $uri = NULL) {
        $this->request = $request;
        
        if ($uri && $uri != $request->uri) { // add content location header
            $this->addHeader('Content-Location', $uri);
            $this->addVary('Accept');
            $this->addVary('Accept-Language');
        }
    }
    
    /**
     * Convert the object into a string suitable for printing
     * @return string
     */
    function __toString() {
        $str = 'HTTP/1.1 '.$this->code;
        foreach ($this->headers as $name => $value) {
            $str .= "\n".$name.': '.$value;
        }
        return $str;
    }
    
    /**
     * Add a header to the response
     * @param string $header
     * @param string $value
     */
    function addHeader($header, $value) {
        $this->headers[$header] = $value;
    }
    
    /**
     * Add content encoding headers and encode the response body
     */
    function doContentEncoding() {
        if (ini_get('zlib.output_compression') == 0) { // do nothing if PHP will do the compression for us
            foreach ($this->request->acceptEncoding as $encoding) {
                switch($encoding) {
                case 'gzip':
                    $this->addHeader('Content-Encoding', 'gzip');
                    $this->addVary('Accept-Encoding');
                    $this->body = gzencode($this->body);
                    return;
                case 'deflate':
                    $this->addHeader('Content-Encoding', 'deflate');
                    $this->addVary('Accept-Encoding');
                    $this->body = gzdeflate($this->body);
                    return;
                case 'compress':
                    $this->addHeader('Content-Encoding', 'compress');
                    $this->addVary('Accept-Encoding');
                    $this->body = gzcompress($this->body);
                    return;
                case 'identity':
                    return;
                }
            }
        }
    }
    
    /**
     * Send a cache control header with the response
     * @param int $time Cache length in seconds
     */
    function addCacheHeader($time = 86400) {
        if ($time) {
            $this->addHeader('Cache-Control', 'max-age='.$time.', must-revalidate');
        } else {
            $this->addHeader('Cache-Control', 'no-cache');
        }
    }
    
    /**
     * Send an etag with the response
     * @param string $etag Etag value
     */
    function addEtag($etag) {
        $this->addHeader('Etag', '"'.$etag.'"');
    }
    
    function addVary($header) {
        if (isset($this->headers['Vary'])) {
            $this->headers['Vary'] .= ' '.$header;
        } else {
            $this->addHeader('Vary', $header);
        }
    }
    
    function output() {        
        if (php_sapi_name() != 'cli' && !headers_sent()) {
            
            if ($this->body) {
                $this->doContentEncoding();
                $this->addHeader('Content-Length', strlen($this->body));
            }
            
            header('HTTP/1.1 '.$this->code);
            foreach ($this->headers as $header => $value) {
                header($header.': '.$value);
            }
        }
        
        echo $this->body;
	}
	
}