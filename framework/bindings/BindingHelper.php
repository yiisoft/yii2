<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use ReflectionNamedType;
use ReflectionParameter;

class BindingHelper
{
    /**
     * @var ReflectionParameter
     * @return string | null
     */
    public static function getParameterTypeName($param)
    {
        if (PHP_VERSION_ID >= 70000 && $param->hasType()) {
            $paramType = $param->getType();
            if ($paramType instanceof ReflectionNamedType) {
                return PHP_VERSION_ID >= 70100 ? $paramType->getName() : (string)$paramType;
            }
        }
        return null;
    }
}