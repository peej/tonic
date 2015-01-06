<?php

namespace Tonic;

class ResourceMetadata implements \ArrayAccess
{
    private $class,
            $namespace,
            $filename,
            $priority = 1,
            $uri = array(),
            $uriParams = array(),
            $methods = array();

    public function __construct($className)
    {
        $metadata = array();

        // get data from reflector
        $classReflector = new \ReflectionClass($className);
        
        $this->class = '\\'.$classReflector->getName();
        $this->namespace = $classReflector->getNamespaceName();
        $this->filename = $classReflector->getFileName();
        $commentString = $this->getDocComment($classReflector);

        if (!$commentString) {
            $startline = $classReflector->getStartLine();
            $fileLines = file($this->filename);
            $lineNumber = $startline - 1;
            while (isset($fileLines[$lineNumber]) && substr($fileLines[$lineNumber], 0, 3) != '/**') {
                $lineNumber--;
            }
            $commentString = join(array_slice($fileLines, $lineNumber));
        }

        // get data from docComment
        $docComment = $this->parseDocComment($commentString);

        if (isset($docComment['@url'])) {
            if (isset($docComment['@uri'])) {
                $docComment['@uri'] = array_merge($docComment['@uri'], $docComment['@url']);
            } else {
                $docComment['@uri'] = $docComment['@url'];
            }
        }
        if (isset($docComment['@uri'])) {
            foreach ($docComment['@uri'] as $uri) {
                $parsedUri = $this->uriTemplateToRegex($uri);
                $this->uri[] = array_shift($parsedUri);
                $this->uriParams[] = $parsedUri;
            }
        }
        if (isset($docComment['@namespace'])) {
            $this->namespace = $docComment['@namespace'][0];
        }
        if (isset($docComment['@priority'])) {
            $this->priority = (int) $docComment['@priority'][0];
        }

        $this->methods = $this->readMethodAnnotations($className);
    }

    public function offsetExists($name)
    {
        return isset($this->$name);
    }

    public function offsetGet($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            $this->$name = $value;
        }
    }

    public function offsetUnset($name)
    {
        $this->$name = null;
    }

    public function getUri($index = null)
    {
        if ($index !== null) {
            return isset($this->uri[$index]) ? $this->uri[$index] : null;
        }

        return $this->uri;
    }

    public function hasUri($uri)
    {
        foreach ($this->uri as $index => $u) {
            if (preg_match('#^'.$this->getUri($index).'$#', $uri)) {
                return true;
            }
        }

        return false;
    }

    public function getUriParams($index = null)
    {
        if ($index !== null) {
            return isset($this->uriParams[$index]) ? $this->uriParams[$index] : null;
        }

        return $this->uriParams;
    }

    public function getMethod($methodName)
    {
        if (isset($this->methods[$methodName])) {
            return $this->methods[$methodName];
        }

        return null;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Append the given URI-space to the resources URLs
     * @param str $uriSpace
     */
    public function mount($uriSpace)
    {
        foreach ($this->uri as $index => $uri) {
            $this->uri[$index] = $uriSpace.$this->uri[$index];
        }
    }

    /**
     * Get the class doccomment from the reflector or from the source file.
     * @return str
     */
    private function getDocComment($classReflector)
    {
        $commentString = $classReflector->getDocComment();

        if (!$commentString) {
            $startline = $classReflector->getStartLine();
            $fileLines = file($classReflector->getFileName());
            $lineNumber = $startline - 1;
            while (isset($fileLines[$lineNumber]) && substr($fileLines[$lineNumber], 0, 3) != '/**') {
                $lineNumber--;
            }
            $commentString = join(array_slice($fileLines, $lineNumber));
        }

        return $commentString;
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
                $parts = explode(' ', $item);
                $key = array_shift($parts);
                $data[$key][] = trim(implode(' ', $parts));
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
            $metadata = $this->mergeMetadata($this->readMethodAnnotations($parentReflector->name, $targetClass), $metadata);
        }
        $interfaces = $classReflector->getInterfaceNames();
        foreach ($interfaces as $interface) {
            $metadata = $this->mergeMetadata($this->readMethodAnnotations($interface, $targetClass), $metadata);
        }

        return $metadata;
    }

    private function mergeMetadata($array1, $array2)
    {
        foreach ($array2 as $method => $metadata) {
            foreach ($metadata->getConditions() as $conditionName => $values) {
                if (!isset($array1[$method])) {
                    $array1[$method] = new MethodMetadata($this->class, $method);
                }
                $array1[$method]->setCondition($conditionName, $values);
            }
        }
        return $array1;
    }

}
