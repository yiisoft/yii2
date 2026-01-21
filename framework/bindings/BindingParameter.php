<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use ReflectionParameter;

final class BindingParameter implements BindingTargetInterface
{
    /**
     * @var ReflectionParameter
     */
    private $parameter;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param ReflectionParameter $parameter
     * @param mixed $value
     */
    public function __construct($parameter, $value = null)
    {
        $this->parameter = $parameter;
        $this->value = $value;
    }

    public function getTarget()
    {
        return $this->parameter;
    }

    public function getName()
    {
        return $this->parameter->getName();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        if (PHP_VERSION_ID >= 70000 && $this->parameter->hasType()) {
            return $this->parameter->getType();
        }
        return null;
    }

    public function getTypeName()
    {
        if ($type = $this->getType()) {
            return PHP_VERSION_ID >= 70100 ? $type->getName() : (string)$type;
        }
        return null;
    }

    public function isArray()
    {
        if (PHP_VERSION_ID >= 80000) {
            return ($type = $this->parameter->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
        } else {
            return $this->parameter->isArray();
        }
        return false;
    }

    public function isBuiltin()
    {
        if (($type = $this->getType()) !== null) {
            return $type->isBuiltin();
        }
        return false;
    }

    public function allowsNull()
    {
        return $this->parameter->allowsNull();
    }

    public function isInstanceOf($typeName)
    {
        $parameterTypeName = $this->getTypeName();
        if ($parameterTypeName) {
            return is_a($parameterTypeName, $typeName, true);
        }
        return false;
    }

    public function hasDefaultValue()
    {
        return $this->parameter->isDefaultValueAvailable();
    }

    public function getDefaultValue()
    {
        return $this->parameter->getDefaultValue();
    }
}
