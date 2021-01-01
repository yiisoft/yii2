<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use DateTimeInterface;
use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class DateTimeBinder extends BaseObject implements ParameterBinderInterface
{
    /**
     * @var array $dateFormats
     */
    public $dateFormats = [
        'Y-m-d',
        'Y-m-d H:i:s',
        DateTimeInterface::ISO8601,
        DateTimeInterface::RFC3339
    ];

    public function bindModel($param, $context)
    {
        $typeName = $param->getTypeName();

        if ($typeName !== "DateTime" && $typeName !== "DateTimeImmutable") {
            return null;
        }

        $value = $param->value;

        if (is_null($value) && $param->allowsNull()) {
            return new BindingResult(null);
        }

        foreach ($this->dateFormats as $format) {
            $result = $typeName::createFromFormat($format, $value);
            if ($result) {
                return new BindingResult($result);
            }
        }

        return null;
    }
}
