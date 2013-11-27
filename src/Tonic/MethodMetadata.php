<?php

namespace Tonic;

class MethodMetadata
{
    private $class,
            $name,
            $conditions = array();

    function __construct($className, $methodName, $docComment)
    {
        $this->class = $className;
        $this->name = $methodName;

        foreach ($docComment as $annotationName => $value) {
            $annotationMethodName = substr($annotationName, 1);
            if (method_exists($className, $annotationMethodName)) {
                foreach ($value as $v) {
                    $this->conditions[$annotationMethodName][] = isset($v[0]) ? $v[0] : null;
                }
            }
        }
    }

    function __call($name, $params)
    {
        if (substr($name, 0, 3) == 'get') {
            $property = strtolower(substr($name, 3, 1)).substr($name, 4);
            if (isset($this->conditions[$property])) {
                return $this->conditions[$property];
            }
        } elseif (substr($name, 0, 3) == 'has' && isset($params[0])) {
            $property = strtolower(substr($name, 3, 1)).substr($name, 4);
            if (isset($this->conditions[$property])) {
                if (is_array($this->conditions[$property])) {
                    return in_array($params[0], $this->conditions[$property]);
                } else {
                    return $this->conditions[$property] == $params[0];
                }
            }
        }
    }

}