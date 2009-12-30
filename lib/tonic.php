<?php

class Request {
    
    var $uri,
        $uris,
        $accept = array(),
        $acceptLang = array();
        
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
    
    private function setDefault() {
        foreach (func_get_args() as $arg) {
            if (isset($arg)) return $arg;
        }
        return NULL;
    }
    
    function __construct($config = array()) {
        
        // set defaults
        $config['uri'] = $this->setDefault($config['uri'], $_SERVER['REQUEST_URI']);
        $config['accept'] = $this->setDefault($config['accept'], $_SERVER['HTTP_ACCEPT']);
        $config['acceptLang'] = $this->setDefault($config['acceptLang'], $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        
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
        
        // create URI list
        foreach ($this->accept as $typeOrder) {
            foreach ($typeOrder as $type) {
                if ($type) {
                    foreach ($this->acceptLang as $langOrder) {
                        foreach ($langOrder as $lang) {
                            if ($lang) {
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
    
    function loadResource() {
        
        var_dump($this->uris);
        
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
