<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders;

class BuiltinTypeBinder extends ParameterTypeFactoryInterace {

    /**
     * @inheritdoc
     */
    public function canCreateType($type) {
        return PHP_VERSION_ID >= 70000 &&
            ($paramType = $type->getType()) !== null &&
            $paramType->isBuiltin();
        // && ($params[$name] !== null || !$type->allowsNull()
    }

    /**
     * @inheritdoc
     */
    public function createType($type, $context) {
        $name = $type->getName();
        $typeName = PHP_VERSION_ID >= 70100 ? $type->getName() : (string)$type;

        $value = $context->params[$name];

        switch ($typeName) {
            case 'int':
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                break;
            case 'float':
                $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                break;
            case 'bool':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
        }

        return $value;
    }
}