<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\BindingTargetInterface;
use yii\bindings\ModelBinderInterface;

final class BuiltinTypeBinder extends BaseObject implements ModelBinderInterface
{
    public function bindModel($target, $context)
    {
        $value = $target->getValue();
        $isArray = $target->isArray();
        $typeName = $target->getTypeName();
        $isBuiltin = $target->isBuiltin();
        $allowsNull = $target->allowsNull();

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
            $value = $this->filterValue($target, $typeName, $value);
            if ($value !== null) {
                return new BindingResult($value);
            }
        }

        if ($value == null && $target->hasDefaultValue()) {
            $value = $target->getDefaultValue();
            return new BindingResult($value);
        }

        return null;
    }

    /**
     * @var BindingTargetInterface $target
     * @var string|null $typeName
     * @var mixed $value
     * @return mixed
     */
    private function filterValue($target, $typeName, $value)
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
