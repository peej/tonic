<?php

namespace Tonic;

class MethodMetadata
{
    private $class,
            $name,
            $conditions = array();

    function __construct($className, $methodName, $docComment = null)
    {
        $this->class = $className;
        $this->name = $methodName;

        if (is_array($docComment)) {
            foreach ($docComment as $annotationName => $values) {
                $annotationMethodName = substr($annotationName, 1);
                if (method_exists($className, $annotationMethodName)) {
                    foreach ($values as $value) {
                        $this->addCondition($annotationMethodName, $value);
                    }
                }
            }
        }
    }

    public function addCondition($condition, $value)
    {
        if (isset($this->conditions[$condition])) {
            $this->conditions[$condition][] = $value;
        } else {
            $this->conditions[$condition] = array($value);
        }
    }

    public function setCondition($condition, $value)
    {
        $this->conditions[$condition] = $value;
    }

    public function hasCondition($condition, $value)
    {
        if (isset($this->conditions[$condition])) {
            if (is_array($this->conditions[$condition])) {
                return in_array($value, $this->conditions[$condition]);
            } else {
                return $this->conditions[$condition] == $value;
            }
        }
        return false;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function getCondition($condition)
    {
        return isset($this->getConditions()[$condition]) ? $this->getConditions()[$condition] : null;
    }

    public function getMethod()
    {
        return $this->getCondition('method');
    }

    public function hasMethod($methodName)
    {
        return $this->hasCondition('method', $methodName);
    }

    public function hasAccepts($mimetype)
    {
        return $this->hasCondition('accepts', $mimetype);
    }

    public function hasProvides($mimetype)
    {
        return $this->hasCondition('provides', $mimetype);
    }
}
