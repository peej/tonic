<?php

namespace Tonic;

class ResourceMetadata
{
    private $app,
            $class,
            $namespace,
            $filename,
            $priority = 1,
            $uri = array(),
            $uriParams = array(),
            $methods = array();

    function __construct($app, $className, $uriSpace)
    {
        $this->app = $app;

        $metadata = array();

        // get data from reflector
        $classReflector = new \ReflectionClass($className);

        $this->class = '\\'.$classReflector->getName();
        $this->namespace = $classReflector->getNamespaceName();
        $this->filename = $classReflector->getFileName();

        // get data from docComment
        $docComment = $this->parseDocComment($classReflector->getDocComment());

        if (isset($docComment['@uri'])) {
            foreach ($docComment['@uri'] as $uri) {
                $parsedUri = $this->uriTemplateToRegex($uri);
                $this->uri[] = $parsedUri[0];
                $this->uriParams[] = isset($parsedUri[1]) ? $parsedUri[1] : array();
            }
        }
        if (isset($docComment['@namespace'])) {
            $this->namespace = $docComment['@namespace'][0][0];
        }
        if (isset($docComment['@priority'])) {
            $this->priority = (int)$docComment['@priority'][0][0];
        }

        $this->methods = $this->readMethodAnnotations($className);
    }

    function __call($name, $params)
    {
        if (substr($name, 0, 3) == 'get') {
            $property = strtolower(substr($name, 3, 1)).substr($name, 4);
            if (is_array($this->$property) && isset($params[0])) {
                return isset($this->$property[$params[0]]) ? $this->$property[$params[0]] : null;
            } else {
                return $this->$property;
            }
        } elseif (substr($name, 0, 3) == 'has' && isset($params[0])) {
            $property = strtolower(substr($name, 3, 1)).substr($name, 4);
            if (isset($this->$property)) {
                if (is_array($this->$property)) {
                    return in_array($params[0], $this->$property);
                } else {
                    return $this->$property == $params[0];
                }
            }
        }
    }

    function getUri($index = null)
    {
        if ($index !== null) {
            return isset($this->uri[$index]) ? $this->app->baseUri.$this->uri[$index] : null;
        }
        return $this->uri;
    }

    function getUriParams($index = null)
    {
        if ($index !== null) {
            return isset($this->uriParams[$index]) ? $this->uriParams[$index] : null;
        }
        return $this->uriParams;
    }

    function getMethod($methodName)
    {
        if (isset($this->methods[$methodName])) {
            return $this->methods[$methodName];
        }
        return null;
    }

    function hasUri($uri)
    {
        foreach ($this->uri as $index => $u) {
            if (preg_match('#^'.$this->getUri($index).'$#', $uri)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Append the given URI-space to the resources URLs
     * @param str $uriSpace
     */
    function mount($uriSpace)
    {
        foreach ($this->uri as $index => $uri) {
            $this->uri[$index] = $uriSpace.$this->uri[$index];
        }
    }

    /**
     * Parse annotations out of a doc comment
     * @param  str   $comment Doc comment to parse
     * @return str[]
     */
    private function parseDocComment($comment)
    {
        $data = array();
        preg_match_all('/^\s*\*[*\s]*(@.+)$/m', $comment, $items);
        if ($items && isset($items[1])) {
            foreach ($items[1] as $item) {
                preg_match_all('/"[^"]+"|[^\s]+/', $item, $parts);
                $key = array_shift($parts[0]);
                array_walk($parts[0], create_function('&$v', '$v = trim($v, \'"\');'));
                $data[$key][] = $parts[0];
            }
        }

        return $data;
    }

    /**
     * Turn a URL template into a regular expression
     * @param  str[] $uri URL template
     * @return str[] Regular expression and parameter names
     */
    private function uriTemplateToRegex($uri)
    {
        preg_match_all('#((?<!\?):[^/]+|{[^0-9][^}]*}|\(.+?\))#', $uri[0], $params, PREG_PATTERN_ORDER);
        $return = $uri;
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

        $return[0] = preg_replace('#((?<!\?):[^(/]+|{[^0-9][^}]*})#', '([^/]+)', $return[0]);

        return $return;
    }

    private function readMethodAnnotations($className, $targetClass = null)
    {
        if (isset($this->methods[$className])) {
            return $this->methods[$className];
        }

        if (!$targetClass) {
            $targetClass = $className;
        }

        $metadata = array();

        $methodNames = get_class_methods($className);
        $methodNames[] = 'setup';
        foreach ($methodNames as $methodName) {
            if (method_exists($className, $methodName)) {

                $methodReflector = new \ReflectionMethod($className, $methodName);
                $methodName = $methodReflector->getName();

                if (($methodReflector->isPublic() || $methodName == 'setup') && $methodReflector->getDeclaringClass()->name != 'Tonic\Resource') {
                    $metadata[$methodName] = new MethodMetadata(
                        $targetClass,
                        $methodName,
                        $this->parseDocComment($methodReflector->getDocComment())
                    );
                }
            }
        }

        // recurse through parent classes and merge in parent class metadata
        $classReflector = new \ReflectionClass($className);
        $parentReflector = $classReflector->getParentClass();
        if ($parentReflector) {
            #$metadata = $this->mergeMetadata($this->readMethodAnnotations($parentReflector->name, $targetClass), $metadata);
        }
        $interfaces = $classReflector->getInterfaceNames();
        foreach ($interfaces as $interface) {
            #$metadata = $this->mergeMetadata($this->readMethodAnnotations($interface, $targetClass), $metadata);
        }

        return $metadata;
    }

    private function mergeMetadata($array1, $array2)
    {
        foreach ($array2 as $method => $metadata) {
            foreach ($metadata as $annotation => $values) {
                foreach ($values as $value) {
                    if (isset($array1[$method][$annotation])) {
                        if (!in_array($value, $array1[$method][$annotation])) {
                            $array1[$method][$annotation][] = $value;
                        }
                    } else {
                        $array1[$method][$annotation] = array($value);
                    }
                }
            }
        }

        return $array1;
    }

}