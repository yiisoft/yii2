<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders\system;

use ReflectionParameter;
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;
use yii\bindings\ParameterInfo;

class BuiltinTypeBinder implements ParameterBinderInterface
{
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
        $name = $param->getName();
        $value = $context->getParameterValue($name);
        $paramInfo = ParameterInfo::fromParameter($param);

        $isArray = $paramInfo->isArray();
        $typeName = $paramInfo->getTypeName();
        $isBuiltin = $paramInfo->isBuiltin();
        $allowsNull = $paramInfo->allowsNull();

        if (!$isBuiltin || !$isArray) {
            return null;
        }

        if ($isArray) {
            return new BindingResult((array)$value);
        }

        if (is_array($value)) {
            return null;
        }

        if ($isBuiltin && (($value !== null) || !$allowsNull)) {
            $value = $this->filterValue($param, $typeName, $value);
            if ($value !== null) {
                return new BindingResult($value);
            } else {
                return null;
            }
        }

        if ($value == null && $param->isDefaultValueAvailable()) {
            $value = $param->getDefaultValue();
            return new BindingResult($value);
        }

        return null;
    }

    /**
     * @var ReflectionParameter $param
     * @var string|null $typeName
     * @var mixed $value
     * @return mixed
     */
    protected function filterValue($param, $typeName, $value)
    {
        switch ($typeName) {
            case 'int':
                return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            case 'float':
                return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        return $value;
    }
}