<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use ReflectionClass;
use ReflectionProperty;

final class BindingProperty implements BindingTargetInterface
{
    /**
     * @var ReflectionProperty
     */
    private $property;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param ReflectionProperty $property
     * @param mixed $value
     */
    public function __construct($property, $value = null)
    {
        $this->property = $property;
        $this->value = $value;
    }

    public function getTarget()
    {
        return $this->property;
    }

    public function getName()
    {
        return $this->property->getName();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        if (PHP_VERSION_ID >= 70000 && $this->property->hasType()) {
            return $this->property->getType();
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
        return $this->getTypeName() === "array";
    }

    public function isBuiltin()
    {
        if ($type = $this->getType()) {
            return $type->isBuiltin();
        }
        return false;
    }

    public function allowsNull()
    {
        if ($type = $this->getType()) {
            return $type->allowsNull();
        }
        return true;
    }

    public function isInstanceOf($typeName)
    {
        $propertyTypeName = $this->getTypeName();
        if ($propertyTypeName) {
            return is_a($propertyTypeName, $typeName, true);
        }
        return false;
    }

    public function hasDefaultValue()
    {
        if (PHP_VERSION_ID >= 80000) {
            return $this->property->hasDefaultValue();
        }
        return true;
    }

    public function getDefaultValue()
    {
        if (PHP_VERSION_ID >= 80000) {
            return $this->property->getDefaultValue();
        }

        $reflectionClass = new ReflectionClass($this->property->class);
        $defaultProperties = $reflectionClass->getDefaultProperties();

        if (isset($defaultProperties[$this->property->name])) {
            return $defaultProperties[$this->property->name];
        }
        return null;
    }
}
