<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\BindingParameter;
use yii\bindings\BindingProperty;

final class TypeReflector
{
    public static function getBindingProperty($type, $name, $value, $defaultValue = null)
    {
        return new BindingProperty(self::propertyOf($type, $name, $defaultValue), $value);
    }

    public static function getBindingParameter($type, $name, $value, $defaultValue = null)
    {
        return new BindingParameter(self::parameterOf($type, $name, $defaultValue), $value);
    }

    public static function parameterOf(string $typeName, $name="value", $defaultValue = null)
    {
        $default = "";
        if ($defaultValue) {
            $default = "= $defaultValue";
        }

        $code = <<<CODE
            return function($typeName \${$name} {$default}) { };
        CODE;

        $reflection = new \ReflectionFunction(eval($code));
        return $reflection->getParameters()[0];
    }

    public static function propertyOf(string $typeName, $name="value", $defaultValue = null)
    {
        $default = "";
        if ($defaultValue) {
            $default = "= $defaultValue";
        }

        $code = <<<CODE
            return new class {
                public $typeName \${$name} {$default}
            };
        CODE;

        $reflection = new \ReflectionClass(eval($code));
        return $reflection->getProperty($name);
    }
}