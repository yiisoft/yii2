<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\base\BaseObject;
use yii\bindings\BindingParameter;
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class BuiltinTypeBinder extends BaseObject implements ParameterBinderInterface
{
    public function bindModel($param, $context)
    {
        $name = $param->getName();
        $value = $param->getValue();

        $isArray = $param->isArray();
        $typeName = $param->getTypeName();
        $isBuiltin = $param->isBuiltin();
        $allowsNull = $param->allowsNull();

        if (!$isBuiltin) {
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
            }
        }

        if ($value == null && $param->isDefaultValueAvailable()) {
            $value = $param->getDefaultValue();
            return new BindingResult($value);
        }

        return null;
    }

    /**
     * @var BindingParameter $param
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
