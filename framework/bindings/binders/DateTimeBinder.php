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
use yii\bindings\ModelBinderInterface;

class DateTimeBinder extends BaseObject implements ModelBinderInterface
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

    public function bindModel($target, $context)
    {
        $typeName = $target->getTypeName();

        if ($typeName !== "DateTime" && $typeName !== "DateTimeImmutable") {
            return null;
        }

        $value = $target->getValue();

        if (is_null($value) && $target->allowsNull()) {
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
