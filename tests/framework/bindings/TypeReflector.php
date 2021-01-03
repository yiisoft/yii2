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
use yii\bindings\BindingProperty;

class TypeReflector
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
}
