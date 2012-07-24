<?php

namespace Tonic;

/**
 * Model a HTTP request
 */
class Request
{
    public $hostname;
    public $uri;
    public $method;
    public $contentType = 'application/x-www-form-urlencoded';
    public $data;
    public $accept = array();
    public $acceptLang = array();
    public $ifMatch = array();
    public $ifNoneMatch = array();
    public $headers = array();

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
        $this->hostname = $this->getOption($options, 'hostname', 'HTTP_HOST');
        $this->uri = $this->getURIFromEnvironment($options);
        $this->method = $this->getOption($options, 'method', 'REQUEST_METHOD', 'GET');

        $this->contentType = $this->getContentType($options);
        $this->data = $this->getData($options);

        $this->accept = array_unique(array_merge($this->accept, $this->getAcceptArray($this->getOption($options, 'accept', 'HTTP_ACCEPT'))));
        $this->acceptLang = array_unique(array_merge($this->acceptLang, $this->getAcceptArray($this->getOption($options, 'acceptLang', 'HTTP_ACCEPT_LANGUAGE'))));

        $this->ifMatch = $this->getMatchArray($this->getOption($options, 'ifMatch', 'HTTP_IF_MATCH'));
        $this->ifNoneMatch = $this->getMatchArray($this->getOption($options, 'ifNoneMatch', 'HTTP_IF_NONE_MATCH'));

        $this->headers = $this->getHeaders($options);
    }

    public function getHeaders(array $options)
    {
        if(isset($options['headers']))
            return $options['headers'];

        $headersList = array_filter(array_keys($_SERVER), function ($headerName) {
            return (0 === strpos($headerName, 'HTTP_'));
        });

        $headersList = array_intersect_key($_SERVER, array_flip($headersList));

        return $headersList;
    }

    /**
     * Get a HTTP response header
     * @param str $name Header name, hyphens should be converted to camelcase
     * @return str
     */
    public function __get($name)
    {
        $name = 'HTTP_' . strtoupper(preg_replace('/([A-Z])/', '_$1', $name));
        return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
    }

    /**
     * Get an item from the given array if it exists otherwise look up in _SERVER superglobal or return default
     * @param  str[] $options
     * @param  str   $configVar Name of item to get
     * @param  str   $serverVar Name of _SERVER superglobal item to use
     * @param  str   $default   Fallback value
     * @return str
     */
    public function getOption($options, $configVar, $serverVar = NULL, $default = NULL)
    {
        if (isset($options[$configVar])) {
            return $options[$configVar];
        } elseif (is_array($serverVar)) {
            foreach ($serverVar as $var) {
                if (isset($_SERVER[$var]) && $_SERVER[$var] != '') {
                    return $_SERVER[$var];
                }
            }
        } elseif (isset($_SERVER[$serverVar]) && $_SERVER[$serverVar] != '') {
            return $_SERVER[$serverVar];
        } else {
            return $default;
        }
    }

    private function getContentType($options)
    {
        $contentType = $this->getOption($options, 'contentType', array('CONTENT_TYPE', 'HTTP_CONTENT_TYPE'));
        $parts = explode(';', $contentType);

        return $parts[0];
    }

    private function getData($options)
    {
        if ($this->getOption($options, 'contentLength', array('CONTENT_LENGTH', 'HTTP_CONTENT_LENGTH')) > 0) {
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
                $uri = substr($_SERVER['REDIRECT_URL'], strlen($dirname == '/' ? '' : $dirname));
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
            }
            if (preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $part)) {
                $this->acceptLang[] = $part;
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
            $parts = explode(';q=', $part);
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
                $acceptArray[] = $part;
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
        $acceptLang = join(', ', $this->acceptLang);
        $ifMatch = join(', ', $this->ifMatch);
        $ifNoneMatch = join(', ', $this->ifNoneMatch);
        $acceptLang = join(', ', $this->acceptLang);

        return <<<EOF
=============
Tonic\Request
=============
Hostname: $this->hostname
URI: $this->uri
HTTP method: $this->method
Content type: $this->contentType
Request data: $this->data
Accept: $accept
Accept language: $acceptLang
If match: $ifMatch
If none match: $ifNoneMatch

EOF;
    }

}
