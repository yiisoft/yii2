<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders\system;

use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class DataFilterBinder implements ParameterBinderInterface
{
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
        $typeName = $param->getType()->getName();
        $dataFilter = new $typeName;
        $dataFilter->load($context->request->getBodyParams());
        return new BindingResult($dataFilter);
    }
}
