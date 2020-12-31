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
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context) {

        $name = $param->getName();
        $value = $context->params[$name];

        if (PHP_VERSION_ID >= 70000)
        {
            $paramType = $param->getType();

            if ($paramType && $paramType->isBuiltin())
            {
                $typeName = PHP_VERSION_ID >= 70100 ? $paramType->getName() : (string)$paramType;

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
                return new BindingResult($value);
            }
        }
        return null;
    }
}
