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
    protected $acceptParams = array();
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

        $accept = $this->getAcceptArrayFromEnvironment($this->getOption($options, 'accept'), $acceptParams);
        $this->acceptParams = $acceptParams;
        $this->accept = array_merge($this->accept, $accept);
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

    public function setUri($uri)
    {
        $this->uri = $uri;
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

    public function setMethod($methodName)
    {
        $this->method = $methodName;
    }

    public function getAccept()
    {
        return $this->accept;
    }

    public function setAccept($value)
    {
        if (is_array($value)) {
            $this->accept = $value;
        } else {
            $this->accept[] = $value;
        }
    }

    public function getAcceptParams()
    {
        return $this->acceptParams;
    }

    public function getAcceptLanguage()
    {
        return $this->acceptLanguage;
    }

    public function setAcceptLanguage($value)
    {
        if (is_array($value)) {
            $this->acceptLanguage = $value;
        } else {
            $this->acceptLanguage[] = $value;
        }
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($mimetype)
    {
        $this->contentType = $mimetype;
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
     * Get accepted content mimetypes and accept parameters from request header
     * @param str $acceptString accept string
     * @param array $params accept parameters
     * @return str[]
     */
    private function getAcceptArrayFromEnvironment($acceptString, &$acceptParamArray = array())
    {
        $accept = $acceptArray = $acceptParam = $acceptParamArray = array();
        $parts = preg_split('/\s*,\s*/', strtolower($acceptString));
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }
            $partParams = preg_split('/\s*;\s*/', $part);
            $type = array_shift($partParams);
            $num = 10;
            $acceptParams = array();
            foreach ($partParams as $param) {
                $keyValue = preg_split('/\s*=\s*/', $param);
                if ($keyValue[0] === 'q') {
                    if (array_key_exists(1, $keyValue) && is_numeric($keyValue[1]) && (int)$keyValue[1] >= 0
                        && (int)$keyValue[1] <= 1) {
                        $num = $keyValue[1] * 10;
                    }
                } else {
                    $key = $keyValue[0];
                    $value = array_key_exists(1, $keyValue) ? $keyValue[1] : null;
                    $acceptParams[] = $key . '=' . $value;
                }
            }
            // quality of 0 is not acceptable to the client - rfc2616 sec 3.9
            if ($num === 0) {
                $type = null;
            }
            if ($type) {
                $accept[$num][] = $type;
                $acceptParam[$num][] = $acceptParams;
            }
        }
        krsort($accept);
        krsort($acceptParam);
        foreach ($accept as $i => $parts) {
            foreach ($parts as $j => $part) {
                $acceptArray[] = $part;
                $acceptParamArray[] = $acceptParam[$i][$j];
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
