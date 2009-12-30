<?php

class Request {
    
    var $uri,
        $uris,
        $accept = array(),
        $acceptLang = array(),
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
        $method,
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
        } else {
            $this->data = file_get_contents("php://input");
        }
        
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
        return new Resource();
        
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
            return $this->{$request->method}();
        }
        
        throw(new Exception('No method found in resource class for "'.$request->method.'"'));
        exit;
        
    }
    
    function get() {
        return new Response(200, 'Something');
    }
    
    
}

class Response {
    
    var $output;
    
    /**
     * Create a response object
     * @var int code
     * @var str body
     */
    function __construct($code, $body) {
        
        $this->output = $body;
        
    }
    
    function output() {
        
        echo $this->output;
        
    }
    
}

?>
