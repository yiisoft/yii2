<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;
use yii\bindings\ParameterInfo;

class DataFilterBinder implements ParameterBinderInterface
{
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
        $paramInfo = ParameterInfo::fromParameter($param);

        if (!$paramInfo->isInstanceOf("yii\\data\\DataFilter")) {
            return null;
        }

        $typeName = $param->getType()->getName();
        $dataFilter = new $typeName;
        $dataFilter->load($context->request->getBodyParams());
        return new BindingResult($dataFilter);
    }
}
