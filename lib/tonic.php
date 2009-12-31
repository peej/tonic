<?php

class Request {
    
    var $uri,
        $uris,
        $accept = array(),
        $acceptLang = array(),
        $acceptEncoding = array(),
        $mimetypes = array(
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
        ),
        $method = 'GET',
        $data;
    
    /**
     * Set a default configuration option
     */
    private function setDefault() {
        foreach (func_get_args() as $arg) {
            if (isset($arg)) return $arg;
        }
        return NULL;
    }
    
    /**
     * Create a request object using the given options
     * @var mixed[] config Configuration options
     */
    function __construct($config = array()) {
        
        // set defaults
        $config['uri'] = $this->setDefault($config['uri'], $_SERVER['REDIRECT_URL']);
        $config['accept'] = $this->setDefault($config['accept'], $_SERVER['HTTP_ACCEPT']);
        $config['acceptLang'] = $this->setDefault($config['acceptLang'], $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $config['acceptEncoding'] = $this->setDefault($config['acceptEncoding'], $_SERVER['HTTP_ACCEPT_ENCODING']);
        
        if (isset($config['mimetypes']) && is_array($config['mimetypes'])) {
            foreach ($config['mimetypes'] as $ext => $mimetype) {
                $this->mimetypes[$ext] = $mimetype;
            }
        }
        
        // get request URI
        $parts = explode('/', $config['uri']);
        $lastPart = array_pop($parts);
        $this->uri = join('/', $parts);
        
        $parts = explode('.', $lastPart);
        $this->uri .= '/'.$parts[0];
        
        array_shift($parts);
        foreach ($parts as $part) {
            $this->accept[10][] = $part;
            $this->acceptLang[10][] = $part;
        }
        
        // sort accept headers
        $accept = explode(',', strtolower($config['accept']));
        foreach ($accept as $mimetype) {
            $parts = explode(';q=', $mimetype);
            if ($parts[1]) {
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
            if ($parts[1]) {
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
        
        // create candidate URI list from accept headers and request URI
        foreach ($this->accept as $typeOrder) {
            foreach ($typeOrder as $type) {
                if ($type) {
                    foreach ($this->acceptLang as $langOrder) {
                        foreach ($langOrder as $lang) {
                            if ($lang && $lang != $type) {
                                $this->uris[] = $this->uri.'.'.$type.'.'.$lang;
                            }
                        }
                    }
                    $this->uris[] = $this->uri.'.'.$type;
                }
            }
        }
        foreach ($this->acceptLang as $langOrder) {
            foreach ($langOrder as $lang) {
                if ($lang) {
                    $this->uris[] = $this->uri.'.'.$lang;
                }
            }
        }
        $this->uris[] = $this->uri;
        $this->uris = array_values(array_unique($this->uris));
        
        // get HTTP method
        $this->method = strtoupper($this->setDefault($config['method'], $_SERVER['REQUEST_METHOD'], $this->method));
        
        // get HTTP request data
        if (isset($config['data'])) {
            $this->data = $config['data'];
        } elseif ($this->method == 'GET') {
            $this->data = $_SERVER['QUERY_STRING'];
        } else {
            $this->data = file_get_contents("php://input");
        }
        
    }
    
	/**
	 * Convert the object into a string suitable for printing
	 * @return str
	 */
	function __toString() {
		$str = 'URI: '.$this->uri."\n";
        $str .= 'Method: '.$this->method."\n";
        $str .= 'Data: '.$this->data."\n";
        $str .= 'Candidate URIs:'."\n";
        foreach ($this->uris as $uri) {
            $str .= "\t".$uri."\n";
        }
		return $str;
	}
    
    /**
     * Instantiate the resource class that matches the request URI the best
     * @return Resource
     */
    function loadResource() {
        
        $uriMatches = array();
        foreach (get_declared_classes() as $className) {
            if (is_subclass_of($className, 'Resource')) {
                $resourceReflector = new ReflectionClass($className);
                $comment = $resourceReflector->getDocComment();
                preg_match_all('/@uri\s+([^\s]+)(?:\s([0-9]+))?/', $comment, $annotations);
                if (isset($annotations[1])) {
                    $uris = $annotations[1];
                } else {
                    $uris = array('/');
                }
                foreach ($uris as $index => $uri) {
                    if (preg_match('|^'.str_replace('|', '\|', $uri).'|', $this->uri)) {
                        if (isset($annotations[2][$index]) && is_numeric($annotations[2][$index])) {
                            $priority = $annotations[2][$index];
                        } else {
                            $priority = 0;
                        }
                        $uriMatches[$priority] = $className;
                    }
                }
            }
        }
        ksort($uriMatches);
        
        if ($uriMatches) {
            $className = array_shift($uriMatches);
            return new $className();
        }
        return new NoResource();
        
    }
    
}

class Resource {
    
    /**
     * Execute a request on this resource
     * @var Request request
     * @return Response
     */
    function exec($request) {
        
        if (method_exists($this, $request->method)) {
            return $this->{$request->method}($request);
        }
        
        // send 405 method not allowed
        $response = new Response($request);
        $response->code = Response::METHODNOTALLOWED;
        $response->body = sprintf(
            'The HTTP method "%s" used for the request is not allowed for the resource "%s".',
            $request->method,
            $request->uri
        );
        return $response;
        
    }
    
}

class NoResource extends Resource {
    
    function exec($request) {
        
        // send 404 not found
        $response = new Response($request);
        $response->code = Response::NOTFOUND;
        $response->body = sprintf(
            'Nothing was found for the resource "%s".',
            $request->uri
        );
        return $response;
        
    }
    
}

class Response {
    
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
    
    var $request,
    $code = Response::OK,
        $headers = array(),
        $body;
    
    /**
     * Create a response object
     * @var Request request
     */
    function __construct($request) {
        
        $this->request = $request;
        
    }
    
	/**
	 * Convert the object into a string suitable for printing
	 * @return str
	 */
	function __toString() {
		$str = 'HTTP/1.1 '.$this->statusCode;
		foreach ($this->headers as $name => $value) {
			$str .= "\n".$name.': '.$value;
		}
		return $str;
	}
    
    function addHeader($header, $value) {
        $this->headers[$header] = $value;
    }
    
	/**
	 * Add content encoding headers and encode the response body
	 */
	function doContentEncoding()
	{
		if (ini_get('zlib.output_compression') == 0) { // do nothing if PHP will do the compression for us
			foreach ($this->request->acceptEncoding as $encoding) {
				switch($encoding) {
				case 'gzip':
					$this->addHeader('Content-Encoding', 'gzip');
					$this->body = gzencode($this->body);
					return;
				case 'deflate':
					$this->addHeader('Content-Encoding', 'deflate');
					$this->body = gzdeflate($this->body);
					return;
				case 'compress':
					$this->addHeader('Content-Encoding', 'compress');
					$this->body = gzcompress($this->body);
					return;
				case 'identity':
					return;
				}
			}
		}
	}
	
	function addCacheHeader($time = 86400) {
	    if ($time) {
	        $this->addHeader('Cache-Control', 'max-age='.$time.', must-revalidate');
	    } else {
	        $this->addHeader('Cache-Control', 'no-cache');
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

?>
