<?php

namespace Tonic;

/**
 * A Tonic application
 */
class Application
{
    /**
     * The base of the URI of all resources in this Tonic Application.
     * It should usually match the value of Apache's mod_rewrite RewriteBase.
     */
    public $baseUri = '';

    /**
     * Application configuration options
     */
    private $options = array();

    /**
     * Metadata of the loaded resources
     */
    public $resources = array();

    public function __construct($options = array())
    {
        if (isset($options['baseUri'])) {
            $this->baseUri = $options['baseUri'];
        } elseif (isset($_SERVER['DOCUMENT_URI'])) {
            $this->baseUri = dirname($_SERVER['DOCUMENT_URI']);
        }
        $this->options = $options;

        // load resource metadata passed in via options array
        if (isset($options['resources']) && is_array($options['resources'])) {
            foreach ($options['resources'] as $resourceMetadata) {
                $this->resources[] = $resourceMetadata;
            }
        }

        $cache = isset($options['cache']) ? $options['cache'] : NULL;
        if ($cache && $cache->isCached()) { // if we've been given a annotation cache, use it
            $this->resources = $cache->load();
        } else { // otherwise load from loaded resource files
            if (isset($options['load'])) { // load given resource class files
                $this->loadResourceFiles($options['load']);
            }
            $this->loadResourceMetadata();
            if ($cache) { // save metadata into annotation cache
                $cache->save($this->resources);
            }
        }

        // set any URI-space mount points we've been given
        if (isset($options['mount']) && is_array($options['mount'])) {
            foreach ($options['mount'] as $namespaceName => $uriSpace) {
                $this->mount($namespaceName, $uriSpace);
            }
        }
    }

    /**
     * Include PHP files containing resources in the given filename globs
     * @paramstr[] $filenames Array of filename globs
     */
    private function loadResourceFiles($filenames)
    {
        if (!is_array($filenames)) {
            $filenames = array($filenames);
        }

        foreach ($filenames as $glob) {
            $globs = glob(str_replace('[', '[[]', $glob));
            if ($globs) {
                foreach ($globs as $filename) {
                    require_once $filename;
                }
            }
        }
    }

    /**
     * Load the metadata for all loaded resource classes
     */
    private function loadResourceMetadata()
    {
        foreach (get_declared_classes() as $className) {
            if (
                !isset($this->resources[$className]) &&
                is_subclass_of($className, 'Tonic\Resource')
            ) {
                $rm = new ResourceMetadata($className);
                if ($rm->getUri()) {
                    $this->resources[$className] = $rm;
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
    public function mount($namespaceName, $uriSpace)
    {
        foreach ($this->resources as $className => $metadata) {
            if ($metadata->getNamespace() == $namespaceName) {
                $metadata->mount($uriSpace);
            }
        }
    }

    /**
     * Get the URL for the given resource class
     *
     * @param  str   $className
     * @param  str[] $params
     * @return str
     */
    public function uri($className, $params = array())
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        if (!isset($this->resources[$className])) {
            throw new \Exception('Resource class "'.$className.'" not found');
        }
        if ($params && !is_array($params)) {
            $params = array($params);
        }
        foreach ($this->resources[$className]->getUri() as $index => $uri) {
            if (count($params) == count($this->resources[$className]->getUriParams($index))) {
                $parts = explode('([^/]+)', $uri);
                $path = '';
                foreach ($parts as $key => $part) {
                    $path .= $part;
                    if (isset($params[$key])) {
                        $path .= $params[$key];
                    }
                }
                return $this->baseUri.$path;
            }
        }
    }

    /**
     * Given the request data and the loaded resource metadata, pick the best matching
     * resource to handle the request based on URI and priority.
     *
     * @deprecated You should use the route method instead.
     * @param  Request  $request
     * @return Resource
     */
    public function getResource($request = NULL)
    {
        if (!$request) {
            $request = new Request();
        }

        $route = $this->route($request);
        $filename = $route->getFilename();
        $className = $route->getClass();

        if ($filename && is_readable($filename)) {
            require_once($filename);
        }

        return new $className($this, $request);
    }

    /**
     * Given the request data and the loaded resource metadata, pick the best matching
     * resource to handle the request based on URI and priority.
     *
     * @param  Request $request
     * @return ResourceMetadata
     */
    public function route($request = NULL)
    {
        $matchedResource = NULL;
        if (!$request) {
            $request = new Request();
        }
        foreach ($this->resources as $className => $resourceMetadata) {
            foreach ($resourceMetadata->getUri() as $index => $uri) {
                $uriRegex = '|^'.$uri.'$|';
                if (
                    ($matchedResource == NULL || $matchedResource[0]->getPriority() < $resourceMetadata->getPriority())
                &&
                    preg_match($uriRegex, $request->getUri(), $params)
                ) {
                    array_shift($params);
                    $uriParams = $resourceMetadata->getUriParams($index);
                    if ($uriParams) { // has params within URI
                        foreach ($uriParams as $key => $name) {
                            $params[$name] = $params[$key];
                        }
                    }
                    $matchedResource = array($resourceMetadata, $params);
                }
            }
        }
        if ($matchedResource) {
            $request->setParams($matchedResource[1]);

            return $matchedResource[0];
        } else {
            throw new NotFoundException(sprintf('Resource matching URI "%s" not found', $request->uri));
        }
    }

    /**
     * Get the already loaded resource annotation metadata
     * @param  Tonic/Resource $resource
     * @return ResourceMetadata
     */
    public function getResourceMetadata($resource)
    {
        if (is_object($resource)) {
            $className = get_class($resource);
        } else {
            $className = $resource;
        }
        if (!isset($this->resources[$className])) {
            throw new \Exception('Resource class "'.$className.'" not found');
        }
        return $this->resources[$className];
    }

    public function __toString()
    {
        $baseUri = $this->baseUri;

        if (isset($this->options['load'])) {
            if (is_array($this->options['load'])) {
                $loadPath = join(', ', $this->options['load']);
            } else {
                $loadPath = $this->options['load'];
            }
        } else $loadPath = '';

        $mount = array();
        if (isset($this->options['mount']) && is_array($this->options['mount'])) {
            foreach ($this->options['mount'] as $namespaceName => $uriSpace) {
                $mount[] = $namespaceName.'="'.$uriSpace.'"';
            }
        }
        $mount = join(', ', $mount);

        $cache = isset($this->options['cache']) ? $this->options['cache'] : NULL;

        $resources = array();
        foreach ($this->resources as $resource) {
            $uri = array();
            foreach ($resource->getUri() as $u) {
                $uri[] = $u;
            }
            $uri = join(', ', $uri);
            $r = $resource->getClass().' '.$uri.' '.$resource->getPriority();
            foreach ($resource->getMethods() as $methodName => $method) {
                $r .= "\n\t\t".$methodName;
                foreach ($method->getConditions() as $itemName => $items) {
                    foreach ($items as $item) {
                        $r .= ' '.$itemName;
                        if ($item) {
                            $r .= '="'.$item.'"';
                        }
                    }
                }
            }
            $resources[] = $r;
        }
        $resources = join("\n\t", $resources);

        return <<<EOF
=================
Tonic\Application
=================
Base URI: $baseUri
Load path: $loadPath
Mount points: $mount
Annotation cache: $cache
Loaded resources:
\t$resources

EOF;
    }

}
