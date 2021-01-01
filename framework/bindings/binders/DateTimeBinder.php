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
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;
use yii\bindings\ParameterInfo;

class DateTimeBinder implements ParameterBinderInterface
{
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
        $name = $param->getName();
        $value = $context->getParameterValue($name);
        $paramInfo = ParameterInfo::fromParameter($param);
        $typeName = $paramInfo->getTypeName();

        if (!$paramInfo->isInstanceOf("\\DateTimeInterface")) {
            return null;
        }

        if (is_null($value) && $paramInfo->allowsNull()) {
            return new BindingResult(null);
        }

        switch ($typeName) {
            case 'DateTime':
                $value = DateTime::createFromFormat(DateTimeInterface::ISO8601, $value);
                break;
            case 'DateTimeImmutable':
                $value = DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, $value);
                break;
            default:
                return null;
        }

        return new BindingResult($value);
    }
}
