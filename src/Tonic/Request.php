<?php

namespace Tonic;

/**
 * Model a HTTP request
 */
class Request
{
    protected $uri;
    protected $params = array();
    protected $method;
    protected $contentType;
    protected $data;
    protected $accept = array();
    protected $acceptLanguage = array();
    protected $ifMatch = array();
    protected $ifNoneMatch = array();

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
        $this->method = $this->getMethodFromEnvironment($options);

        $this->contentType = $this->getContentTypeFromEnvironment($options);
        $this->data = $this->getDataFromEnvironment($options);

        $this->accept = array_unique(array_merge($this->accept, $this->getAcceptArrayFromEnvironment($this->getOption($options, 'accept'))));
        $this->acceptLanguage = array_unique(array_merge($this->acceptLanguage, $this->getAcceptArrayFromEnvironment($this->getOption($options, 'acceptLanguage'))));

        $this->ifMatch = $this->getMatchArrayFromEnvironment($this->getOption($options, 'ifMatch'));
        $this->ifNoneMatch = $this->getMatchArrayFromEnvironment($this->getOption($options, 'ifNoneMatch'));
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
     * Also gets private member via getter without explicitly using the getter.
     *
     * @param str name
     * @return str
     */
    public function __get($name)
    {
        if (method_exists($this, 'get'.ucfirst($name))) {
            return $this->{'get'.ucfirst($name)}();
        }
        return $this->getHeader($name);
    }

    /**
     * Magic PHP method to set a private member without explicitly using the setter.
     *
     * @param str name
     * @param mixed value
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set'.ucfirst($name))) {
            return $this->{'set'.ucfirst($name)}($value);
        }
        throw new Exception('Could not set property "'.$name.'"');
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getAccept()
    {
        return $this->accept;
    }

    public function getAcceptLanguage()
    {
        return $this->acceptLanguage;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getIfMatch()
    {
        return $this->ifMatch;
    }

    public function getIfNoneMatch()
    {
        return $this->ifNoneMatch;
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

    private function getMethodFromEnvironment($options)
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

    private function getContentTypeFromEnvironment($options)
    {
        $contentType = $this->getOption($options, 'contentType');
        $parts = explode(';', $contentType);

        return $parts[0];
    }

    private function getDataFromEnvironment($options)
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
        $parts = explode('/', $uri);
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
    private function getAcceptArrayFromEnvironment($acceptString)
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
    private function getMatchArrayFromEnvironment($matchString)
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
