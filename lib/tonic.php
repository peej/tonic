<?php
/*
 * This file is part of the Tonic.
 * (c) Paul James <paul@peej.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* Turn on namespace in PHP5.3 if you have namespace collisions from the Tonic classnames
namespace Tonic;
use \ReflectionClass as ReflectionClass;
use \ReflectionMethod as ReflectionMethod;
use \Exception as Exception;
//*/

/**
 * Model the data of the incoming HTTP request
 * @namespace Tonic\Lib
 */
class Request {
    
    /**
     * The requested URI
     * @var str
     */
    public $uri;
    
    /**
     * The URI where the front controller is positioned in the server URI-space
     * @var str
     */
    public $baseUri = '';
    
    /**
     * Array of possible URIs based upon accept and accept-language request headers in order of preference
     * @var str[]
     */
    public $negotiatedUris = array();
    
    /**
     * Array of possible URIs based upon accept request headers in order of preference
     * @var str[]
     */
    public $formatNegotiatedUris = array();
    
    /**
     * Array of possible URIs based upon accept-language request headers in order of preference
     * @var str[]
     */
    public $languageNegotiatedUris = array();
    
    /**
     * Array of accept headers in order of preference
     * @var str[][]
     */
    public $accept = array();
    
    /**
     * Array of accept-language headers in order of preference
     * @var str[][]
     */
    public $acceptLang = array();
    
    /**
     * Array of accept-encoding headers in order of preference
     * @var str[]
     */
    public $acceptEncoding = array();
    
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
    
    /**
     * Supported HTTP methods
     * @var str[]
     */
    public $HTTPMethods = array(
        'GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'
    );
    
    /**
     * Allowed resource methods
     * @var str[]
     */
    public $allowedMethods = array();
    
    /**
     * HTTP request method of incoming request
     * @var str
     */
    public $method = 'GET';
    
    /**
     * Body data of incoming request
     * @var str
     */
    public $data;
    
    /**
     * Array of if-match etags
     * @var str[]
     */
    public $ifMatch = array();
    
    /**
     * Array of if-none-match etags
     * @var str[]
     */
    public $ifNoneMatch = array();
    
    /**
     * The resource classes loaded and how they are wired to URIs
     * @var str[]
     */
    public $resources = array();
    
    /**
     * A list of URL to namespace/package mappings for routing requests to a
     * group of resources that are wired into a different URL-space
     * @var str[]
     */
    public $mounts = array();
    
    /**
     * Set a default configuration option
     */
    protected function getConfig($config, $configVar, $serverVar = NULL, $default = NULL) {
        if (isset($config[$configVar])) {
            return $config[$configVar];
        } elseif (isset($_SERVER[$serverVar]) && $_SERVER[$serverVar] != '') {
            return $_SERVER[$serverVar];
        } else {
            return $default;
        }
    }
    
    /**
     * Create a request object using the given configuration options.
     *
     * The configuration options array can contain the following:
     *
     * <dl>
     * <dt>uri</dt> <dd>The URI of the request</dd>
     * <dt>method</dt> <dd>The HTTP method of the request</dd>
     * <dt>data</dt> <dd>The body data of the request</dd>
     * <dt>accept</dt> <dd>An accept header</dd>
     * <dt>acceptLang</dt> <dd>An accept-language header</dd>
     * <dt>acceptEncoding</dt> <dd>An accept-encoding header</dd>
     * <dt>ifMatch</dt> <dd>An if-match header</dd>
     * <dt>ifNoneMatch</dt> <dd>An if-none-match header</dd>
     * <dt>mimetypes</dt> <dd>A map of file/URI extenstions to mimetypes, these
     * will be added to the default map of mimetypes</dd>
     * <dt>baseUri</dt> <dd>The base relative URI to use when dispatcher isn't
     * at the root of the domain. Do not put a trailing slash</dd>
     * <dt>mounts</dt> <dd>an array of namespace to baseUri prefix mappings</dd>
     * <dt>HTTPMethods</dt> <dd>an array of HTTP methods to support</dd>
     * </dl>
     *
     * @param mixed[] config Configuration options
     */
    function __construct($config = array()) {
        
        // set defaults
        $config['uri'] = parse_url($this->getConfig($config, 'uri', 'REQUEST_URI'), PHP_URL_PATH);
        $config['baseUri'] = $this->getConfig($config, 'baseUri', '');
        $config['accept'] = $this->getConfig($config, 'accept', 'HTTP_ACCEPT');
        $config['acceptLang'] = $this->getConfig($config, 'acceptLang', 'HTTP_ACCEPT_LANGUAGE');
        $config['acceptEncoding'] = $this->getConfig($config, 'acceptEncoding', 'HTTP_ACCEPT_ENCODING');
        $config['ifMatch'] = $this->getConfig($config, 'ifMatch', 'HTTP_IF_MATCH');
        $config['ifNoneMatch'] = $this->getConfig($config, 'ifNoneMatch', 'HTTP_IF_NONE_MATCH');
        
        if (isset($config['mimetypes']) && is_array($config['mimetypes'])) {
            foreach ($config['mimetypes'] as $ext => $mimetype) {
                $this->mimetypes[$ext] = $mimetype;
            }
        }
        
        // set baseUri
        $this->baseUri = $config['baseUri'];
        
        // get request URI
        $parts = explode('/', $config['uri']);
        $lastPart = array_pop($parts);
        $this->uri = join('/', $parts);
        
        $parts = explode('.', $lastPart);
        $this->uri .= '/'.$parts[0];
        
        if ($this->uri != '/' && substr($this->uri, -1, 1) == '/') { // remove trailing slash problem
            $this->uri = substr($this->uri, 0, -1);
        }
        
        array_shift($parts);
        foreach ($parts as $part) {
            $this->accept[10][] = $part;
            $this->acceptLang[10][] = $part;
        }
        
        // sort accept headers
        $accept = explode(',', strtolower($config['accept']));
        foreach ($accept as $mimetype) {
            $parts = explode(';q=', $mimetype);
            if (isset($parts) && isset($parts[1]) && $parts[1]) {
                $num = $parts[1] * 10;
            } else {
                $num = 10;
            }
            $key = array_search($parts[0], $this->mimetypes);
            if ($key) {
                $this->accept[$num][] = $key;
            }
        }
        krsort($this->accept);
        
        // sort lang accept headers
        $accept = explode(',', strtolower($config['acceptLang']));
        foreach ($accept as $mimetype) {
            $parts = explode(';q=', $mimetype);
            if (isset($parts) && isset($parts[1]) && $parts[1]) {
                $num = $parts[1] * 10;
            } else {
                $num = 10;
            }
            $this->acceptLang[$num][] = $parts[0];
        }
        krsort($this->acceptLang);
        
        // get encoding accept headers
        if ($config['acceptEncoding']) {
            foreach (explode(',', $config['acceptEncoding']) as $key => $accept) {
                $this->acceptEncoding[$key] = trim($accept);
            }
        }
        
        // create negotiated URI lists from accept headers and request URI
        foreach ($this->accept as $typeOrder) {
            foreach ($typeOrder as $type) {
                if ($type) {
                    foreach ($this->acceptLang as $langOrder) {
                        foreach ($langOrder as $lang) {
                            if ($lang && $lang != $type) {
                                $this->negotiatedUris[] = $this->uri.'.'.$type.'.'.$lang;
                            }
                        }
                    }
                    $this->negotiatedUris[] = $this->uri.'.'.$type;
                    $this->formatNegotiatedUris[] = $this->uri.'.'.$type;
                }
            }
        }
        foreach ($this->acceptLang as $langOrder) {
            foreach ($langOrder as $lang) {
                if ($lang) {
                    $this->negotiatedUris[] = $this->uri.'.'.$lang;
                    $this->languageNegotiatedUris[] = $this->uri.'.'.$lang;
                }
            }
        }
        $this->negotiatedUris[] = $this->uri;
        $this->formatNegotiatedUris[] = $this->uri;
        $this->languageNegotiatedUris[] = $this->uri;
        
        $this->negotiatedUris = array_values(array_unique($this->negotiatedUris));
        $this->formatNegotiatedUris = array_values(array_unique($this->formatNegotiatedUris));
        $this->languageNegotiatedUris = array_values(array_unique($this->languageNegotiatedUris));
        
        $this->HTTPMethods = $this->getConfig($config, 'HTTPMethods', NULL, $this->HTTPMethods);
        
        // get HTTP method
        $this->method = strtoupper($this->getConfig($config, 'method', 'REQUEST_METHOD', $this->method));
        
        // get HTTP request data
        $this->data = $this->getConfig($config, 'data', NULL, file_get_contents("php://input"));
        
        // conditional requests
        if ($config['ifMatch']) {
            $ifMatch = explode(',', $config['ifMatch']);
            foreach ($ifMatch as $etag) {
                $this->ifMatch[] = trim($etag, '" ');
            }
        }
        if ($config['ifNoneMatch']) {
            $ifNoneMatch = explode(',', $config['ifNoneMatch']);
            foreach ($ifNoneMatch as $etag) {
                $this->ifNoneMatch[] = trim($etag, '" ');
            }
        }
        
        // mounts
        if (isset($config['mount']) && is_array($config['mount'])) {
            $this->mounts = $config['mount'];
        }
        
        // prime named resources for autoloading
        if (isset($config['autoload']) && is_array($config['autoload'])) {
            foreach ($config['autoload'] as $uri => $className) {
                $this->resources[$uri] = array(
                    'class' => $className,
                    'loaded' => FALSE
                );
            }
        }
        
        // load definitions of already loaded resource classes
        $resourceClassName = class_exists('Tonic\\Resource') ? 'Tonic\\Resource' : 'Resource';
        foreach (get_declared_classes() as $className) {
            if (is_subclass_of($className, $resourceClassName)) {
                
                $resourceDetails = $this->getResourceClassDetails($className);
                
                preg_match_all('/@uri\s+([^\s]+)(?:\s([0-9]+))?/', $resourceDetails['comment'], $annotations);
                if (isset($annotations[1]) && $annotations[1]) {
                    $uris = $annotations[1];
                } else {
                    $uris = array();
                }
                
                foreach ($uris as $index => $uri) {
                    if ($uri != '/' && substr($uri, -1, 1) == '/') { // remove trailing slash problem
                        $uri = substr($uri, 0, -1);
                    }
                    $this->resources[$resourceDetails['mountPoint'].$uri] = array(
                        'namespace' => $resourceDetails['namespaceName'],
                        'class' => $resourceDetails['className'],
                        'filename' => $resourceDetails['filename'],
                        'line' => $resourceDetails['line'],
                        'priority' => isset($annotations[2][$index]) && is_numeric($annotations[2][$index]) ? intval($annotations[2][$index]) : 0,
                        'loaded' => TRUE
                    );
                }
            }
        }
        
    }
    
    /**
     * Get the details of a Resource class by reflection
     * @param str className
     * @return str[]
     */
    protected function getResourceClassDetails($className) {
        
        $resourceReflector = new ReflectionClass($className);
        $comment = $resourceReflector->getDocComment();
        
        $className = $resourceReflector->getName();
        if (method_exists($resourceReflector, 'getNamespaceName')) {
            $namespaceName = $resourceReflector->getNamespaceName();
        } else {
            // @codeCoverageIgnoreStart
            $namespaceName = FALSE;
            // @codeCoverageIgnoreEnd
        }
        
        if (!$namespaceName) {
            preg_match('/@(?:package|namespace)\s+([^\s]+)/', $comment, $package);
            if (isset($package[1])) {
                $namespaceName = $package[1];
            }
        }
        
        // adjust URI for mountpoint
        if (isset($this->mounts[$namespaceName])) {
            $mountPoint = $this->mounts[$namespaceName];
        } else {
            $mountPoint = '';
        }
        
        return array(
            'comment' => $comment,
            'className' => $className,
            'namespaceName' => $namespaceName,
            'filename' => $resourceReflector->getFileName(),
            'line' => $resourceReflector->getStartLine(),
            'mountPoint' => $mountPoint
        );
    
    }
    
    /**
     * Convert the object into a string suitable for printing
     * @return str
     * @codeCoverageIgnore
     */
    function __toString() {
        $str = 'URI: '.$this->uri."\n";
        $str .= 'Method: '.$this->method."\n";
        if ($this->data) {
            $str .= 'Data: '.$this->data."\n";
        }
        $str .= 'Acceptable Formats:';
        foreach ($this->accept as $accept) {
            foreach ($accept as $a) {
                $str .= ' .'.$a;
                if (isset($this->mimetypes[$a])) $str .= ' ('.$this->mimetypes[$a].')';
            }
        }
        $str .= "\n";
        $str .= 'Acceptable Languages:';
        foreach ($this->acceptLang as $accept) {
            foreach ($accept as $a) {
                $str .= ' '.$a;
            }
        }
        $str .= "\n";
        $str .= 'Negotated URIs:'."\n";
        foreach ($this->negotiatedUris as $uri) {
            $str .= "\t".$uri."\n";
        }
        $str .= 'Format Negotated URIs:'."\n";
        foreach ($this->formatNegotiatedUris as $uri) {
            $str .= "\t".$uri."\n";
        }
        $str .= 'Language Negotated URIs:'."\n";
        foreach ($this->languageNegotiatedUris as $uri) {
            $str .= "\t".$uri."\n";
        }
        if ($this->ifMatch) {
            $str .= 'If Match:';
            foreach ($this->ifMatch as $etag) {
                $str .= ' '.$etag;
            }
            $str .= "\n";
        }
        if ($this->ifNoneMatch) {
            $str .= 'If None Match:';
            foreach ($this->ifNoneMatch as $etag) {
                $str .= ' '.$etag;
            }
            $str .= "\n";
        }
        $str .= 'Loaded Resources:'."\n";
        foreach ($this->resources as $uri => $resource) {
            $str .= "\t".$uri."\n";
            if (isset($resource['namespace']) && $resource['namespace']) $str .= "\t\tNamespace: ".$resource['namespace']."\n";
            $str .= "\t\tClass: ".$resource['class']."\n";
            $str .= "\t\tFile: ".$resource['filename'];
            if (isset($resource['line']) && $resource['line']) $str .= '#'.$resource['line'];
            $str .= "\n";
        }
        return $str;
    }
    
    /**
     * Instantiate the resource class that matches the request URI the best
     * @return Resource
     * @throws ResponseException If the resource does not exist, a 404 exception is thrown
     */
    function loadResource() {
        
        $uriMatches = array();
        foreach ($this->resources as $uri => $resource) {
            
            preg_match_all('#((?<!\?):[^/]+|{[^0-9][^}]*}|\(.+?\))#', $uri, $params, PREG_PATTERN_ORDER);
            
            $uri = $this->baseUri.$uri;
            if ($uri != '/' && substr($uri, -1, 1) == '/') { // remove trailing slash problem
                $uri = substr($uri, 0, -1);
            }
            $uriRegex = preg_replace('#((?<!\?):[^(/]+|{[^0-9][^}]*})#', '(.+)', $uri);
            
            if (preg_match('#^'.$uriRegex.'$#', $this->uri, $matches)) {
                array_shift($matches);
                
                if (isset($params[1])) {
                    foreach ($params[1] as $index => $param) {
                        if (isset($matches[$index])) {
                            if (substr($param, 0, 1) == ':') {
                                $matches[substr($param, 1)] = $matches[$index];
                                unset($matches[$index]);
                            } elseif (substr($param, 0, 1) == '{' && substr($param, -1, 1) == '}') {
                                $matches[substr($param, 1, -1)] = $matches[$index];
                                unset($matches[$index]);
                            }
                        }
                    }
                }
                
                $uriMatches[isset($resource['priority']) ? $resource['priority'] : 0] = array(
                    $uri,
                    $resource,
                    $matches
                );
                
            }
        }
        ksort($uriMatches);
        
        if ($uriMatches) {
            list($uri, $resource, $parameters) = array_shift($uriMatches);
            if (!$resource['loaded']) { // autoload
                if (!class_exists($resource['class'])) {
                    throw new Exception('Unable to load resource');
                }
                $resourceDetails = $this->getResourceClassDetails($resource['class']);
                $resource = $this->resources[$uri] = array(
                    'namespace' => $resourceDetails['namespaceName'],
                    'class' => $resourceDetails['className'],
                    'filename' => $resourceDetails['filename'],
                    'line' => $resourceDetails['line'],
                    'priority' => 0,
                    'loaded' => TRUE
                );
            }
            
            $this->allowedMethods = array_intersect(array_map('strtoupper', get_class_methods($resource['class'])), $this->HTTPMethods);
            
            return new $resource['class']($parameters);
        }
        
        // no resource found, throw response exception
        throw new ResponseException('A resource matching URI "'.$this->uri.'" was not found', Response::NOTFOUND);
        
    }
    
    /**
     * Check if an etag matches the requests if-match header
     * @param str etag Etag to match
     * @return bool
     */
    function ifMatch($etag) {
        if (isset($this->ifMatch[0]) && $this->ifMatch[0] == '*') {
            return TRUE;
        }
        return in_array($etag, $this->ifMatch);
    }
    
    /**
     * Check if an etag matches the requests if-none-match header
     * @param str etag Etag to match
     * @return bool
     */
    function ifNoneMatch($etag) {
        if (isset($this->ifMatch[0]) && $this->ifMatch[0] == '*') {
            return FALSE;
        }
        return in_array($etag, $this->ifNoneMatch);
    }
    
    /**
     * Return the most acceptable of the given formats based on the accept array
     * @param str[] formats
     * @return str
     */
    function mostAcceptable($formats) {
        foreach (call_user_func_array('array_merge', $this->accept) as $format) {
            if (in_array($format, $formats)) {
                return $format;
            }
        }
        return NULL;
    }
    
}

/**
 * Base resource class
 * @namespace Tonic\Lib
 */
class Resource {
    
    private $parameters;
    
    /**
     * Resource constructor
     * @param str[] parameters Parameters passed in from the URL as matched from the URI regex
     */
    function  __construct($parameters) {
        $this->parameters = $parameters;
    }
    
    /**
     * Convert the object into a string suitable for printing
     * @return str
     * @codeCoverageIgnore
     */
    function __toString() {
        $str = get_class($this);
        foreach ($this->parameters as $name => $value) {
            $str .= "\n".$name.': '.$value;
        }
        return $str;
    }
    
    /**
     * Execute a request on this resource.
     * @param Request request The request to execute the resource in the context of
     * @return Response
     * @throws ResponseException If the HTTP method is not allowed on the resource, a 405 exception is thrown
     */
    function exec($request) {
        
        if (
            in_array(strtoupper($request->method), $request->HTTPMethods) &&
            method_exists($this, $request->method)
        ) {
            
            $method = new ReflectionMethod($this, $request->method);
            $parameters = array();
            foreach ($method->getParameters() as $param) {
                if ($param->name == 'request') {
                    $parameters[] = $request;
                } elseif (isset($this->parameters[$param->name])) {
                    $parameters[] = $this->parameters[$param->name];
                    unset($this->parameters[$param->name]);
                } else {
                    $parameters[] = reset($this->parameters);
                    array_shift($this->parameters);
                }
            }
            
            $response = call_user_func_array(
                array($this, $request->method),
                $parameters
            );
            
            $responseClassName = class_exists('Tonic\\Response') ? 'Tonic\\Response' : 'Response';
            if (!$response || !is_a($response, $responseClassName)) {
                throw new Exception('Method '.$request->method.' of '.get_class($this).' did not return a Response object');
            }
            
        } else {
            
            // send 405 method not allowed
            throw new ResponseException(
                'The HTTP method "'.$request->method.'" is not allowed for the resource "'.$request->uri.'".',
                Response::METHODNOTALLOWED
            );
            
        }
        
        # good for debugging, remove this at some point
        $response->addHeader('X-Resource', get_class($this));
        
        return $response;
        
    }
    
}

/**
 * Model the data of the outgoing HTTP response
 * @namespace Tonic\Lib
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
     * @var Request
     */
    public $request;
    
    /**
     * The HTTP response code to send
     * @var int
     */
    public $code = Response::OK;
    
    /**
     * The HTTP headers to send
     * @var str[]
     */
    public $headers = array();
    
    /**
     * The HTTP response body to send
     * @var str
     */
    public $body;
    
    /**
     * Create a response object.
     * @param Request request The request object generating this response
     * @param str uri The URL of the actual resource being used to build the response
     */
    function __construct($request, $uri = NULL) {
        
        $this->request = $request;
        
        if ($uri && $uri != $request->uri) { // add content location header
            $this->addHeader('Content-Location', $uri);
            $this->addVary('Accept');
            $this->addVary('Accept-Language');
        }
        $this->addHeader('Allow', implode(', ', $request->allowedMethods));
        
    }
    
    /**
     * Convert the object into a string suitable for printing
     * @return str
     * @codeCoverageIgnore
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
     * @param str header
     * @param str value
     */
    function addHeader($header, $value) {
        $this->headers[$header] = $value;
    }
    
    /**
     * Send a cache control header with the response
     * @param int time Cache length in seconds
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
     * @param str etag Etag value
     */
    function addEtag($etag) {
        $this->addHeader('Etag', '"'.$etag.'"');
    }
    
    function addVary($header) {
        if (isset($this->headers['Vary'])) {
            $this->headers['Vary'] .= ', '.$header;
        } else {
            $this->addHeader('Vary', $header);
        }
    }
    
    /**
     * Output the response
     * @codeCoverageIgnore
     */
    function output() {
        
        if (php_sapi_name() != 'cli' && !headers_sent()) {
            
            header('HTTP/1.1 '.$this->code);
            foreach ($this->headers as $header => $value) {
                header($header.': '.$value);
            }
        }
        
        if (strtoupper($this->request->method) !== 'HEAD') {
            echo $this->body;
        }
        
    }
    
}

/**
 * Exception class for HTTP response errors
 * @namespace Tonic\Lib
 */
class ResponseException extends Exception {
    
    /**
     * Generate a default response for this exception
     * @param Request request
     * @return Response
     */
    function response($request) {
        $response = new Response($request);
        $response->code = $this->code;
        $response->body = $this->message;
        return $response;
    }
    
}

?>
