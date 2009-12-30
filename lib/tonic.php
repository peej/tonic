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
        $resourcePath = '../resources';
    
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
        $config['uri'] = $this->setDefault($config['uri'], $_SERVER['REQUEST_URI']);
        $config['accept'] = $this->setDefault($config['accept'], $_SERVER['HTTP_ACCEPT']);
        $config['acceptLang'] = $this->setDefault($config['acceptLang'], $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        
        if (isset($config['mimetypes']) && is_array($config['mimetypes'])) {
            foreach ($config['mimetypes'] as $ext => $mimetype) {
                $this->mimetypes[$ext] = $mimetype;
            }
        }
            
        $this->resourcePath = $this->setDefault($config['resourcePath'], $this->resourcePath);
        
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
    }
    
    /**
     * Instantiate the resource class that matches the request URI the best
     */
    function loadResource() {
        
        $uriMatches = array();
        foreach (get_declared_classes() as $className) {
            if (is_subclass_of($className, 'Resource')) {
                $resourceReflector = new ReflectionClass($className);
                $comment = $resourceReflector->getDocComment();
                preg_match('/@uri\s+(.+)/', $comment, $uri);
                if (isset($uri[1])) {
                    $uri = $uri[1];
                } else {
                    $uri = '/';
                }
                if (preg_match('|^'.str_replace('|', '\|', $uri).'|', $this->uri)) {
                    preg_match('/@priority\s+(.+)/', $comment, $priority);
                    if (isset($priority[1]) && is_numeric($priority[1])) {
                        $priority = $priority[1];
                    } else {
                        $priority = 0;
                    }
                    $uriMatches[$priority] = $className;
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
    
    function exec() {
        
        return new Response();
        
    }
    
}

class Response {
    
    function output() {
        
        echo "hi";
        
    }
    
}

?>
