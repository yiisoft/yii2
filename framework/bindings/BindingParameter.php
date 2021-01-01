<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use ReflectionParameter;
use ReflectionProperty;

interface BindingTargetInterface
{
    public function getTarget();
    public function getValue();
    public function getName();
    public function getType();
    public function getTypeName();
    public function isArray();
    public function isBuiltin();
    public function isDefaultValueAvailable();
    public function getDefaultValue();
}


final class BindingParameter
{
    /**
     * @var ReflectionParameter|ReflectionProperty
     */
    public $parameter;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @param ReflectionParameter $parameter
     */
    public function __construct($parameter, $value = null)
    {
        $this->parameter = $parameter;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->parameter->getName();
    }

    /**
     * @return string | null
     */
    public function getTypeName()
    {
        if (PHP_VERSION_ID >= 70000 && $this->parameter->hasType()) {
            $paramType = $this->parameter->getType();
            return PHP_VERSION_ID >= 70100 ? $paramType->getName() : (string)$paramType;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isArray()
    {
        if ($this->parameter instanceof ReflectionParameter) {
            if (PHP_VERSION_ID >= 80000) {
                return ($type = $this->parameter->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
            } else {
                return $this->parameter->isArray();
            }
        } elseif ($this->parameter instanceof ReflectionProperty) {
            return ($type = $this->parameter->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isBuiltin()
    {
        if (PHP_VERSION_ID >= 70000 && $this->parameter->hasType()) {
            return $this->parameter->getType()->isBuiltin();
        }
        return false;
    }

    /**
     * @return bool
     */
    public function allowsNull()
    {
        if ($this->parameter instanceof ReflectionProperty) {
            if ($this->parameter->hasType() && $this->parameter->getType()->getName() == "array") {
                return true;
            }
            return false;
        }

        return $this->parameter->allowsNull();
    }

    /**
     * @param string $typeName
     * @return bool
     */
    public function isInstanceOf($typeName)
    {
        $parameterTypeName = $this->getTypeName();
        if ($parameterTypeName) {
            return is_a($parameterTypeName, $typeName, true);
        }
    }

    public function isDefaultValueAvailable()
    {
        if ($this->parameter instanceof ReflectionProperty) {
            return false;
        }

        return $this->parameter->isDefaultValueAvailable();
    }

    public function getDefaultValue()
    {
        return $this->parameter->getDefaultValue();
    }
}
