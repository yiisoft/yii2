<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders\system;

use yii\binders\BindingResult;
use yii\binders\ParameterBinderInterface;

class BuiltinTypeBinder extends ParameterBinderInterface {
    /**
     * @inheritdoc
     */
    public function bindModel($type, $context) {

    //     return PHP_VERSION_ID >= 70000 &&
    //     ($paramType = $type->getType()) !== null &&
    //     $paramType->isBuiltin();
    // // && ($params[$name] !== null || !$type->allowsNull()

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
