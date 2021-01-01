<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

final class ParameterInfo
{
    /**
     * @var ReflectionParameter
     */
    private $parameter;

    /**
     * @param ReflectionParameter $parameter
     */
    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @var ReflectionParameter $parameter
     * @return ParameterInfo
     */
    public static function fromParameter($parameter)
    {
        return new ParameterInfo($parameter);
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
        if (PHP_VERSION_ID >= 80000) {
            return ($type = $this->parameter->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
        } else {
            return $this->parameter->isArray();
        }
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
        return $this->parameter->allowsNull();
    }

    /**
     * @param string $typeName
     * @return bool
     */
    public function isInstanceOf($typeName)
    {
        try {
            $reflectionClass = new ReflectionClass($this->getTypeName());
            return $reflectionClass->implementsInterface($typeName);

            //return $reflectionClass->isInstance($typeName);
        } catch (ReflectionException $ex) {
            return false;
        }
    }
}
