<?php

namespace Tonic;

/**
 * Model a HTTP request
 */
class Request
{
    public $uri;
    public $params = array();
    public $method;
    public $contentType;
    public $data;
    public $accept = array();
    public $acceptLanguage = array();
    public $ifMatch = array();
    public $ifNoneMatch = array();

    /**
     * Map of file/URI extensions to mimetypes
     * @var str[]
     */
    public $mimetypes = array(
        'html' => 'text/html',
        'txt' => 'text/plain',
        'php' => 'application/php',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'gz' => 'application/x-gzip',
        'tar' => 'application/x-tar',
        'zip' => 'application/zip',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'ico' => 'image/x-icon',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        'avi' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg'
    );

    public function __construct($options = array())
    {
        if (isset($options['mimetypes']) && is_array($options['mimetypes'])) {
            foreach ($options['mimetypes'] as $ext => $mimetype) {
                $this->mimetypes[$ext] = $mimetype;
            }
        }

        $this->uri = $this->getURIFromEnvironment($options);
        $this->params = $this->getOption($options, 'params', null, array());
        $this->method = $this->getMethod($options);

        $this->contentType = $this->getContentType($options);
        $this->data = $this->getData($options);

        $this->accept = array_unique(array_merge($this->accept, $this->getAcceptArray($this->getOption($options, 'accept'))));
        $this->acceptLanguage = array_unique(array_merge($this->acceptLanguage, $this->getAcceptArray($this->getOption($options, 'acceptLanguage'))));

        $this->ifMatch = $this->getMatchArray($this->getOption($options, 'ifMatch'));
        $this->ifNoneMatch = $this->getMatchArray($this->getOption($options, 'ifNoneMatch'));
    }

    /**
     * Get an item from the given options array if it exists, otherwise fetch from HTTP header
     * or return the given default
     *
     * @param  str[] $options
     * @param  str $configVar Name of item to get
     * @param  str|str[] $headers Name of HTTP header(s)
     * @param  str $default Fallback value
     * @return str
     */
    public function getOption($options, $configVar, $headers = null, $default = null)
    {
        if (isset($options[$configVar])) {
            return $options[$configVar];
        } else {
            if (!is_array($headers)) {
                $headers = array($headers);
            }
            $headers[] = $configVar;
            foreach ($headers as $header) {
                if ($val = $this->getHeader($header)) {
                    return $val;
                }
            }
            return $default;
        }
    }

    /**
     * Magic PHP method to retrieve a HTTP request header.
     *
     * For example, to retrieve the content-type header, camelcase the header name:
     *
     *   $request->userAgent
     *
     * @param str name
     * @return str
     */
    public function __get($name)
    {
        return $this->getHeader($name);
    }

    private function getHeader($name)
    {
        $name = strtoupper(preg_replace('/([A-Z])/', '_$1', $name));
        if (isset($_SERVER['HTTP_'.$name])) {
            return $_SERVER['HTTP_'.$name];
        } elseif (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        } else {
            return NULL;
        }
    }

    private function getMethod($options)
    {
        // get HTTP method from HTTP header
        $method = strtoupper($this->getHeader('requestMethod'));
        if (!$method) {
            $method = 'GET';
        }

        // get override value from override HTTP header and use if applicable
        $override = strtoupper($this->getHeader('xHttpMethodOverride'));
        if ($override && $method == 'POST') {
            $method = $override;
        
        } else {
            // get override value from URL and use if applicable
            if (
                isset($options['uriMethodOverride']) &&
                $method == 'POST'
            ) {
                // get override value from appended bang syntax
                if (preg_match('/![A-Z]+$/', $this->uri, $match, PREG_OFFSET_CAPTURE)) {
                    $method = strtoupper(substr($this->uri, $match[0][1] + 1));
                    $this->uri = substr($this->uri, 0, $match[0][1]);
                
                // get override value from _method querystring
                } elseif (isset($_GET['_method'])) {
                    $method = strtoupper($_GET['_method']);
                }
            }
        }

        return $this->getOption($options, 'method', null, $method);
    }

    private function getContentType($options)
    {
        $contentType = $this->getOption($options, 'contentType');
        $parts = explode(';', $contentType);

        return $parts[0];
    }

    private function getData($options)
    {
        if ($this->getOption($options, 'contentLength') > 0) {
            return file_get_contents('php://input');
        } elseif (isset($options['data'])) {
            return $options['data'];
        }
    }

    /**
     * Fetch the request URI from the server environment
     * @param  str $options
     * @return str
     */
    private function getURIFromEnvironment($options)
    {
        $uri = $this->getOption($options, 'uri');
        if (!$uri) { // use given URI in config options
            if (isset($_SERVER['REDIRECT_URL']) && isset($_SERVER['SCRIPT_NAME'])) { // use redirection URL from Apache environment
                $dirname = dirname($_SERVER['SCRIPT_NAME']);
                $uri = substr($_SERVER['REDIRECT_URL'], strlen($dirname == DIRECTORY_SEPARATOR ? '' : $dirname));
            } elseif (isset($_SERVER['REQUEST_URI'])) { // use request URI from environment
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            } elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['SCRIPT_NAME'])) { // use PHP_SELF from Apache environment
                $uri = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']));
            } else { // fail
                throw new \Exception('URI not provided');
            }
        }

        // get mimetype
        $parts = explode('/', rtrim($uri, '/'));
        $lastPart = array_pop($parts);
        $uri = join('/', $parts);

        $parts = explode('.', $lastPart);
        $firstPart = array_shift($parts);
        $uri .= '/'.$firstPart;

        foreach ($parts as $part) {
            if (isset($this->mimetypes[$part])) {
                $this->accept[] = $this->mimetypes[$part];
            } elseif (preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $part)) {
                $this->acceptLanguage[] = $part;
            } else {
                $uri .= '.'.$part;
            }
        }

        return $uri;
    }

    /**
     * Get accepted content mimetypes from request header
     * @param  str   $acceptString
     * @return str[]
     */
    private function getAcceptArray($acceptString)
    {
        $accept = $acceptArray = array();
        foreach (explode(',', strtolower($acceptString)) as $part) {
            $parts = preg_split('/\s*;\s*q=/', $part);
            if (isset($parts) && isset($parts[1]) && $parts[1]) {
                $num = $parts[1] * 10;
            } else {
                $num = 10;
            }
            if ($parts[0]) {
                $accept[$num][] = $parts[0];
            }
        }
        krsort($accept);
        foreach ($accept as $parts) {
            foreach ($parts as $part) {
                $acceptArray[] = trim($part);
            }
        }

        return $acceptArray;
    }

    /**
     * Get if-match data from request header
     * @param  str   $matchString
     * @return str[]
     */
    private function getMatchArray($matchString)
    {
        $matches = array();
        foreach (explode(',', $matchString) as $etag) {
            $matches[] = trim($etag, '" ');
        }

        return $matches;
    }

    public function __toString()
    {
        $accept = join(', ', $this->accept);
        $acceptLanguage = join(', ', $this->acceptLanguage);
        $ifMatch = join(', ', $this->ifMatch);
        $ifNoneMatch = join(', ', $this->ifNoneMatch);

        return <<<EOF
=============
Tonic\Request
=============
URI: $this->uri
HTTP method: $this->method
Content type: $this->contentType
Request data: $this->data
Accept: $accept
Accept language: $acceptLanguage
If match: $ifMatch
If none match: $ifNoneMatch

EOF;
    }

}
