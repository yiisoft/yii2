<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\ModelBinderInterface;

final class DateTimeBinder extends BaseObject implements ModelBinderInterface
{
    /**
     * @var array $dateFormats
     */
    public $dateFormats = [
        'Y-m-d\TH:i:s.u\Z'=> ['resetTime' => false],
        'Y-m-d H:i:s'=> ['resetTime' => false],
        'Y-m-d' => ['resetTime' => true]
    ];

    public function bindModel($target, $context)
    {
        $typeName = $target->getTypeName();

        if ($typeName !== "DateTime" && $typeName !== "DateTimeImmutable") {
            return null;
        }

        $value = $target->getValue();
        $result = null;

        foreach ($this->dateFormats as $format => $options) {
            $result = DateTime::createFromFormat($format, $value);

            if ($result) {
               //  print_r([$format, $value, $result]);
                if (isset($options['resetTime']) && $options['resetTime']) {
                    $result->setTime(0, 0, 0);
                }
                break;
            }
        }

        if ($result) {
            if ($typeName === "DateTimeImmutable") {
                $result = DateTimeImmutable::createFromMutable($result);
            }
            return new BindingResult($result);
        }

        if ($target->allowsNull()) {
            return new BindingResult(null);
        }

        return null;
    }
}
