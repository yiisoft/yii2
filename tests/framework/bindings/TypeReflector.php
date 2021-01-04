<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use DateTime;
use DateTimeImmutable;
use ReflectionProperty;
use yii\bindings\BindingParameter;
use yii\bindings\BindingProperty;

final class TypeReflector
{
    public array $array;
    public int $int;
    public float $float;
    public bool $bool;
    public string $string;

    public ?int $nullable_int;
    public ?float $nullable_float;
    public ?bool $nullable_bool;
    public ?string $nullable_string;

    public DateTime $DateTime;
    public DateTimeImmutable $DateTimeImmutable;

    public \yii\data\DataFilter $yii_data_DataFilter;
    public \yii\data\ActiveDataFilter $yii_data_ActiveDataFilter;

    /**
     * @deprecated
     */
    public static function getReflectionProperty($name)
    {
        $name = str_replace("?", "nullable_", $name);
        $name = str_replace("\\", "_", $name);
        return new ReflectionProperty(self::class, $name);
    }

    public static function getBindingTarget($name, $value)
    {
        return new BindingProperty(self::getReflectionProperty($name), $value);
    }

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
