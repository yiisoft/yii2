<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\bindings\ParameterBinderInterface;

class DateTimeBinder implements ParameterBinderInterface
{
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
    }
}
