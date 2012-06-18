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
    public $negotiatedUris = array();
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
            $this->loadResourceMetadata();
            if ($cache) {
                $cache->save($this->resources);
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
        } elseif (isset($_SERVER['QUERY_STRING'])) { // use querystring if not using redirection
            if ($pos = strpos($_SERVER['QUERY_STRING'], '?')) {
                $uri = substr($_SERVER['QUERY_STRING'], 0, $pos);
                parse_str(substr($_SERVER['QUERY_STRING'], $pos + 1), $_GET);
            } else {
                $uri = $_SERVER['QUERY_STRING'];
            }
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
                $this->resources[$className] = $this->getResourceMetadata($className);
                if ($uriSpace) {
                    $this->resources[$className]['uri'][0] = '|^'.$uriSpace.substr($this->resources[$className]['uri'][0], 2);
                }
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
                $this->resources[$className]['uri'][0] = '|^'.$uriSpace.substr($this->resources[$className]['uri'][0], 2);
            }
        }
    }

    public function loadResource() {
        $matchedResource = NULL;
        foreach ($this->resources as $className => $resourceMetadata) {
            if (isset($resourceMetadata['uri'])) {
                if (is_array($resourceMetadata['uri']) && isset($resourceMetadata['uri'][0])) {
                    $uriRegex = $resourceMetadata['uri'][0];
                } else {
                    $uriRegex = $resourceMetadata['uri'];
                }
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
                    if (is_array($resourceMetadata['uri']) && count($resourceMetadata['uri']) > 1) {
                        $params = array_combine($resourceMetadata['uri'], $params);
                        array_shift($params);
                    }
                    $matchedResource = array($resourceMetadata, $params);
                }
            }
        }
        if ($matchedResource) {
            if (isset($matchedResource[0]['filename'])) {
                require_once($matchedResource[0]['filename']);
            }
            return new $matchedResource[0]['class']($this, $matchedResource[1]);
        } else {
            throw new NotFoundException(sprintf('Resource matching URI "%s" not found', $this->uri));
        }
    }

    private function getResourceMetadata($className) {
        $metadata = array();

        // get data from reflector
        $classReflector = new \ReflectionClass($className);
        $metadata['class'] = $classReflector->getName();
        $metadata['namespace'] = $classReflector->getNamespaceName();
        $metadata['priority'] = 1;

        // get data from docComment
        $docComment = $this->parseDocComment($classReflector->getDocComment());

        if (isset($docComment['@uri'])) {
            $metadata['uri'] = $this->uriTemplateToRegex($docComment['@uri'][0]);
        }
        if (isset($docComment['@namespace'])) $metadata['namespace'] = $docComment['@namespace'][0];
        if (isset($docComment['@priority'])) $metadata['priority'] = $docComment['@priority'][0];

        return $metadata;
    }

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

    public function getMethodMetadata($resource) {

        $className = get_class($resource);
        if (isset($this->resources[$className]['method'])) {
            return $this->resources[$className]['method'];
        }

        $metadata = array();

        foreach (get_class_methods($resource) as $methodName) {
            $methodMetadata = array();

            $methodReflector = new \ReflectionMethod($resource, $methodName);

            $docComment = $this->parseDocComment($methodReflector->getDocComment());
            if (isset($docComment['@method'])) {
                $methodMetadata['method'] = $docComment['@method'];
                if (isset($docComment['@accepts'])) $methodMetadata['accepts'] = $docComment['@accepts'];
                if (isset($docComment['@provides'])) $methodMetadata['provides'] = $docComment['@provides'];
                if (isset($docComment['@condition'])) {
                    foreach ($docComment['@condition'] as $condition) {
                        $params = preg_split('/\s+/', $condition);
                        $name = array_shift($params);
                        $methodMetadata[$name] = $params;
                    }
                }
                $metadata[$methodReflector->getName()] = $methodMetadata;
            }
        }

        return $metadata;
    }

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

}