<?php

namespace Tonic;

/**
 * Models a HTTP request
 */
class Request {

    public $hostname;
    public $uri;
    public $method;
    public $contentType = 'application/x-www-form-urlencoded';
    public $data;
    public $accept = array();
    public $acceptLang = array();
    public $ifMatch = array();
    public $ifNoneMatch = array();

    private $resources = array();

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

    function __construct($options = array()) {
        $this->hostname = $this->getOption($options, 'hostname', 'HTTP_HOST');
        $this->uri = $this->getURIFromEnvironment($options);
        $this->method = $this->getOption($options, 'method', 'REQUEST_METHOD', 'GET');

        if (isset($_SERVER['CONTENT_LENGTH']) && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $this->contentType = $_SERVER['CONTENT_TYPE'];
            $this->data = file_get_contents('php://input');
        } elseif (isset($options['contentType']) && isset($options['data'])) {
            $this->contentType = $options['contentType'];
            $this->data = $options['data'];
        }

        $this->accept = array_unique(array_merge($this->accept, $this->getAcceptArray($this->getOption($options, 'accept', 'HTTP_ACCEPT'))));
        $this->acceptLang = array_unique(array_merge($this->acceptLang, $this->getAcceptArray($this->getOption($options, 'acceptLang', 'HTTP_ACCEPT_LANGUAGE'))));

        $this->ifMatch = $this->getMatchArray($this->getOption($options, 'ifMatch', 'HTTP_IF_MATCH'));
        $this->ifNoneMatch = $this->getMatchArray($this->getOption($options, 'ifNoneMatch', 'HTTP_IF_NONE_MATCH'));

        // load resource metadata passed in via options array
        $this->resources = $this->getOption($options, 'resources', NULL, array());

        $cache = $this->getOption($options, 'cache', NULL);
        if ($cache && $cache->isCached()) { // if we've been given a metadata cache, use it
            $this->resources = $cache->load();
        } else {
            if (isset($options['load'])) { // load given resource class files
                $this->loadResourceFiles($options['load']);
            }
            $this->loadResourceMetadata();
            if ($cache) {
                $cache->save($this->resources);
            }
        }

        if (isset($options['mount']) && is_array($options['mount'])) {
            foreach ($options['mount'] as $namespaceName => $uriSpace) {
                $this->mount($namespaceName, $uriSpace);
            }
        }
    }

    private function loadResourceFiles($filenames) {
        if (!is_array($filenames)) {
            $filenames = array($filenames);
        }

        foreach ($filenames as $glob) {
            foreach (glob($glob) as $filename) {
                require_once $filename;
            }
        }
    }

    private function getOption($options, $configVar, $serverVar = NULL, $default = NULL) {
        if (isset($options[$configVar])) {
            return $options[$configVar];
        } elseif (isset($_SERVER[$serverVar]) && $_SERVER[$serverVar] != '') {
            return $_SERVER[$serverVar];
        } else {
            return $default;
        }
    }

    private function getURIFromEnvironment($options) {
        if (isset($options['uri'])) { // use given URI in config options
            $uri = $options['uri'];
        } elseif (isset($_SERVER['REDIRECT_URL']) && isset($_SERVER['SCRIPT_NAME'])) { // use redirection URL from Apache environment
            $uri = substr($_SERVER['REDIRECT_URL'], strlen(dirname($_SERVER['SCRIPT_NAME'])));
        } elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['SCRIPT_NAME'])) { // use PHP_SELF from Apache environment
            $uri = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']));
        } else { // fail
            throw new \Exception('URI not provided');
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
            }
            if (preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $part)) {
                $this->acceptLang[] = $part;
            }
        }

        return $uri;
    }

    private function getAcceptArray($acceptString) {
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
        foreach($accept as $parts) {
            foreach ($parts as $part) {
                $acceptArray[] = $part;
            }
        }
        return $acceptArray;
    }

    private function getMatchArray($matchString) {
        $matches = array();
        foreach (explode(',', $matchString) as $etag) {
            $matches[] = trim($etag, '" ');
        }
        return $matches;
    }

    public function loadResourceMetadata($uriSpace = NULL) {
        foreach (get_declared_classes() as $className) {
            if (
                !isset($this->resources[$className]) &&
                is_subclass_of($className, 'Tonic\Resource')
            ) {
                $this->resources[$className] = $this->readResourceAnnotations($className);
                if ($uriSpace) {
                    $this->resources[$className]['uri'][0] = '|^'.$uriSpace.substr($this->resources[$className]['uri'][0], 2);
                }
                $this->resources[$className]['methods'] = $this->readMethodAnnotations($className);
            }
        }
    }

    /**
     * Add a namespace to a specific URI-space
     *
     * @param str $namespaceName
     * @param str $uriSpace
     */
    public function mount($namespaceName, $uriSpace) {
        foreach ($this->resources as $className => $metadata) {
            if ($metadata['namespace'] == $namespaceName) {
                foreach ($metadata['uri'] as $index => $uri) {
                    $this->resources[$className]['uri'][$index][0] = '|^'.$uriSpace.substr($uri[0], 2);
                }
            }
        }
    }

    /**
     * Get the URL for the given resource class
     *
     * @param str $className
     * @param str[] $params
     * @return str
     */
    public function uri($className, $params = array()) {
        if (is_object($className)) {
            $className = get_class($className);
        }
        if (isset($this->resources[$className])) {
            if ($params && !is_array($params)) {
                $params = array($params);
            }
            foreach ($this->resources[$className]['uri'] as $uri) {
                if (count($params) == count($uri) - 1) {
                    $parts = explode('([^/]+)', $uri[0]);
                    $path = '';
                    foreach ($parts as $key => $part) {
                        $path .= $part;
                        if (isset($params[$key])) {
                            $path .= $params[$key];
                        }
                    }
                    return substr($path, 2, -2);
                }
            }
        }
    }

    /**
     * Given the request data and the loaded resource metadata, pick the best matching
     * resource to handle the request based on URI and priority.
     *
     * @return Resource
     */
    public function getResource() {
        $matchedResource = NULL;
        foreach ($this->resources as $className => $resourceMetadata) {
            if (isset($resourceMetadata['uri'])) {
                if (!is_array($resourceMetadata['uri'])) {
                    $resourceMetadata['uri'] = array($resourceMetadata['uri']);
                }
                foreach ($resourceMetadata['uri'] as $uri) {
                    if (!is_array($uri)) {
                        $uri = array($uri);
                    }
                    $uriRegex = $uri[0];
                    if (!isset($resourceMetadata['priority'])) {
                        $resourceMetadata['priority'] = 1;
                    }
                    if (!isset($resourceMetadata['class'])) {
                        $resourceMetadata['class'] = $className;
                    }
                    if (
                        ($matchedResource == NULL || $matchedResource[0]['priority'] < $resourceMetadata['priority'])
                    &&
                        preg_match($uriRegex, $this->uri, $params)
                    ) {
                        if (count($uri) > 1) { // has params within URI
                            $params = array_combine($uri, $params);
                        }
                        array_shift($params);
                        $matchedResource = array($resourceMetadata, $params);
                    }
                }
            }
        }
        if ($matchedResource) {
            if (isset($matchedResource[0]['filename']) && is_readable($matchedResource[0]['filename'])) {
                require_once($matchedResource[0]['filename']);
            }
            return new $matchedResource[0]['class']($this, $matchedResource[1]);
        } else {
            throw new NotFoundException(sprintf('Resource matching URI "%s" not found', $this->uri));
        }
    }

    /**
     * Get the already loaded resource annotation metadata
     * @param  Tonic/Resource $resource
     * @return str[]
     */
    public function getResourceMetadata($resource) {
        if (is_object($resource)) {
            $className = get_class($resource);
        } else {
            $className = $resource;
        }
        return isset($this->resources[$className]) ? $this->resources[$className] : NULL;
    }

    /**
     * Read the annotation metadata for the given class
     * @return  str[] Annotation metadata
     */
    private function readResourceAnnotations($className) {
        $metadata = array();

        // get data from reflector
        $classReflector = new \ReflectionClass($className);

        $metadata['class'] = '\\'.$classReflector->getName();
        $metadata['namespace'] = $classReflector->getNamespaceName();
        $metadata['filename'] = $classReflector->getFileName();
        $metadata['priority'] = 1;

        // get data from docComment
        $docComment = $this->parseDocComment($classReflector->getDocComment());

        if (isset($docComment['@uri'])) {
            foreach ($docComment['@uri'] as $uri) {
                $metadata['uri'][] = $this->uriTemplateToRegex($uri);
            }
        }
        if (isset($docComment['@namespace'])) $metadata['namespace'] = $docComment['@namespace'][0];
        if (isset($docComment['@priority'])) $metadata['priority'] = $docComment['@priority'][0];

        return $metadata;
    }

    /**
     * Turn a URL template into a regular expression
     * @param  str $uri URL template
     * @return str      Regular expression
     */
    private function uriTemplateToRegex($uri) {
        preg_match_all('#((?<!\?):[^/]+|{[^0-9][^}]*}|\(.+?\))#', $uri, $params, PREG_PATTERN_ORDER);
        $return = array($uri);
        if (isset($params[1])) {
            foreach ($params[1] as $index => $param) {
                if (substr($param, 0, 1) == ':') {
                    $return[] = substr($param, 1);
                } elseif (substr($param, 0, 1) == '{' && substr($param, -1, 1) == '}') {
                    $return[] = substr($param, 1, -1);
                } else {
                    $return[] = $index;
                }
            }
        }

        $return[0] = '|^'.preg_replace('#((?<!\?):[^(/]+|{[^0-9][^}]*})#', '([^/]+)', $return[0]).'$|';
        return $return;
    }

    public function readMethodAnnotations($className) {

        if (isset($this->resources[$className]) && isset($this->resources[$className]['methods'])) {
            return $this->resources[$className]['methods'];
        }

        $metadata = array();

        foreach (get_class_methods($className) as $methodName) {
            $methodMetadata = array();

            $methodReflector = new \ReflectionMethod($className, $methodName);

            $docComment = $this->parseDocComment($methodReflector->getDocComment());
            if (isset($docComment['@method'])) {
                foreach ($docComment as $annotationName => $value) {
                    $methodName = substr($annotationName, 1);
                    if (method_exists($className, $methodName)) {
                        $methodMetadata[$methodName] = $value;
                    }
                }
                $metadata[$methodReflector->getName()] = $methodMetadata;
            }
        }

        return $metadata;
    }

    /**
     * Parse annotations out of a doc comment
     * @param  str $comment Doc comment to parse
     * @return str[]
     */
    private function parseDocComment($comment) {
        $data = array();
        preg_match_all('/^\s*\*\s*(@.+)$/m', $comment, $items);
        if ($items && isset($items[1])) {
            foreach ($items[1] as $item) {
                $parts = preg_split('/\s+/', $item);
                if ($parts) {
                    $key = array_shift($parts);
                    if (isset($data[$key])) {
                        $data[$key][] = join(' ', $parts);
                    } else {
                        $data[$key] = array(join(' ', $parts));
                    }
                }
            }
        }
        return $data;
    }

    public function __toString() {
        $accept = join(', ', $this->accept);
        $acceptLang = join(', ', $this->acceptLang);
        $ifMatch = join(', ', $this->ifMatch);
        $ifNoneMatch = join(', ', $this->ifNoneMatch);
        $acceptLang = join(', ', $this->acceptLang);
        $resources = array();
        foreach ($this->resources as $resource) {
            $uri = array();
            foreach($resource['uri'] as $u) {
                $uri[] = $u[0];
            }
            $uri = join(', ', $uri);
            $r = $resource['class'].' '.$uri.' '.$resource['priority'];
            foreach ($resource['methods'] as $methodName => $method) {
                $r .= "\n\t\t".$methodName;
                foreach ($method as $itemName => $item) {
                    $r .= ' '.$itemName.'="'.join(', ', $item).'"';
                }
            }
            $resources[] = $r;
        }
        $resources = join("\n\t", $resources);
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
Loaded resources:
\t$resources

EOF;
    }

}