<?php

namespace Tonic;

use Tonic\Cache as Cache;

/**
 * Model the data of the incoming HTTP request
 */
class Request {
	
    /**
     * The requested URI
     * @var string
     */
    var $uri;
    
    /**
     * The URI where the front controller is positioned in the server URI-space
     * @var string
     */
    var $baseUri = '';
    
    /**
     * Array of possible URIs based upon accept and accept-language request headers in order of preference
     * @var string[]
     */
    var $negotiatedUris = array();
    
    /**
     * Array of possible URIs based upon accept request headers in order of preference
     * @var string[]
     */
    var $formatNegotiatedUris = array();
    
    /**
     * Array of possible URIs based upon accept-language request headers in order of preference
     * @var string[]
     */
    var $languageNegotiatedUris = array();
    
    /**
     * Array of accept headers in order of preference
     * @var string[][]
     */
    var $accept = array();
    
    /**
     * Array of accept-language headers in order of preference
     * @var string[][]
     */
    var $acceptLang = array();
    
    /**
     * Array of accept-encoding headers in order of preference
     * @var string[]
     */
    var $acceptEncoding = array();
    
    /**
     * Map of file/URI extensions to mimetypes
     * @var string[]
     */
    var $mimetypes = array(
            'html' => 'text/html',
            'txt' => 'text/plain',
            'php' => 'application/php',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'text/xml',
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
     * HTTP request method of incoming request
     * @var string
     */
    var $method = 'GET';
    
    /**
     * Body data of incoming request
     * @var string
     */
    var $data;
    
    /**
     * Array of if-match etags
     * @var string[]
     */
    var $ifMatch = array();
    
    /**
     * Array of if-none-match etags
     * @var string[]
     */
    var $ifNoneMatch = array();
    
    /**
     * Name of resource class to use for when nothing is found
     * @var string
     */
    var $noResource = 'Tonic\NoResource';
    
    /**
     * The resource classes loaded and how they are wired to URIs
     * @var string[]
     */
    var $resources = array();
    
    /**
     * A list of URL to namespace/package mappings for routing requests to a
     * group of resources that are wired into a different URL-space
     * @var string[]
     */
    var $mounts = array();
    
    /**
     * Set a default configuration option
     * @return string
     */
    private function getConfig($config, $configVar, $serverVar = NULL, $default = NULL) {
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
     * </dl>
     *
     * @param mixed[] $config Configuration options
     */
    function __construct($config = array()) {
        
        // set defaults
        $config['uri'] = $this->getConfig($config, 'uri', 'REDIRECT_URL');
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
        
        if (substr($this->uri, -1, 1) == '/') { // remove trailing slash problem
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
        
        // 404 resource
        if (isset($config['404'])) {
            $this->noResource = $config['404'];
        }
        
        // mounts
        if (isset($config['mount']) && is_array($config['mount'])) {
            $this->mounts = $config['mount'];
        }
        
        // resource classes
        // try to load from cache first
        if ($cacheConfig = $this->getConfig($config, 'cache')) {
        	$cache = Cache\Factory::getCache($cacheConfig['type']);
        	$resources = $cache->get('resources', $this->getConfig($cacheConfig, 'options', null, array()));
        }
        
        // if that fails then parse Tonic\Resource classes for data
        if (!isset($resources) || !$resources) {
	        foreach (get_declared_classes() as $className) {
	            if (is_subclass_of($className, 'Tonic\Resource')) {
	                
	                $resourceReflector = new \ReflectionClass($className);
	                
	                $comment = $resourceReflector->getDocComment();	                
	                $className = $resourceReflector->getName();	                    
	                $namespaceName = $resourceReflector->getNamespaceName();
	                
	                preg_match_all('/@uri\s+([^\s]+)(?:\s([0-9]+))?/', $comment, $annotations);
	                if (isset($annotations[1])) {
	                    $uris = $annotations[1];
	                } else {
	                    $uris = array('/');
	                }
	                
	                // adjust URI for mountpoint
	                if (isset($this->mounts[$namespaceName])) {
	                    $mountPoint = $this->mounts[$namespaceName];
	                } else {
	                    $mountPoint = '';
	                }
	                
	                foreach ($uris as $index => $uri) {
	                    if (substr($uri, -1, 1) == '/') { // remove trailing slash problem
	                        $uri = substr($uri, 0, -1);
	                    }
	                    
	                    // parse uri into tokens
	                    $uriparts = explode('/', $uri);
	                    $uriparts = array_slice($uriparts, 2);
	                    // build parameter array from tokens
	                    $uriparams = array();
	                    foreach($uriparts as $token) {
	                    	if (substr($token, 0, 1) == ':') { // check for colon because token may be hardcoded value
	                    		$uriparams[] = substr($token, 1);
	                    	}
	                    }
	                    // build regexp to match uri
	                    foreach($uriparams as $param) {
	                    	$uri = preg_replace('#:'.$param.'(?=\b)#', '([^/]+)', $uri);
	                    }
	                    
	                    $this->resources[$mountPoint.$uri] = array(
	                        'namespace' => $namespaceName,
	                        'class' => $className,
	                        'filename' => $resourceReflector->getFileName(),
	                        'line' => $resourceReflector->getStartLine(),
	                        'priority' => isset($annotations[2][$index]) && is_numeric($annotations[2][$index]) ? intval($annotations[2][$index]) : 0,
	                    	'params' => $uriparams
	                    );
	                }
	            }
	        }
	        
	        // save to cache
	        if (isset($cache) && $cache instanceof Cache\Type) $cachewritesuccess = $cache->set('resources', $this->resources);
        } else {
        	$this->resources = $resources;
        }
        
    }
    
    /**
     * Convert the object into a string suitable for printing
     * @return string
     */
    function __toString() {
        $str = 'URI: '.$this->uri."\n";
        $str .= 'Method: '.$this->method."\n";
        if ($this->data) {
            $str .= 'Data: '.$this->data."\n";
        }
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
            if ($resource['namespace']) $str .= "\t\tNamespace: ".$resource['namespace']."\n";
            $str .= "\t\tClass: ".$resource['class']."\n";
            if ($resource['params']) {
	            $str .= "\t\tParameters: ";
	            foreach ($resource['params'] as $key => $value) {
	            	$str .= $value;
	            	if (count($resource['params']) > 1 && $key != (count($resource['params'])) - 1) $str .= ', ';
	            }
	            $str .= "\n";
            }
            $str .= "\t\tFile: ".$resource['filename'].'#'.$resource['line']."\n";
        }
        return $str;
    }
    
    /**
     * Instantiate the resource class that matches the request URI the best
     * @return Tonic\Resource
     */
    function loadResource() {
    	$uriMatches = array();
        foreach ($this->resources as $uri => $resource) {
            if (preg_match('#^'.$this->baseUri.$uri.'$#', $this->uri, $matches)) {
                array_shift($matches);
                $uriMatches[$resource['priority']] = array(
                    $resource['class'],
                    (count($matches) > 0 && count($matches) ==  count($resource['params'])) ? 
                    		array_combine($resource['params'], $matches) : $matches
                );
            }
        }
        ksort($uriMatches);
        
        if ($uriMatches) {
            $resourceDetails = array_shift($uriMatches);
            return new $resourceDetails[0]($resourceDetails[1]);
        }
        return new $this->noResource();
    }
    
    /**
     * Check if an etag matches the requests if-match header
     * @param string etag Etag to match
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
     * @param string etag Etag to match
     * @return bool
     */
    function ifNoneMatch($etag) {
        if (isset($this->ifMatch[0]) && $this->ifMatch[0] == '*') {
            return FALSE;
        }
        return in_array($etag, $this->ifNoneMatch);
    }
    
}