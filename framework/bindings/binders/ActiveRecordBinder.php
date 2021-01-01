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
use yii\helpers\VarDumper;

class ActiveRecordBinder implements ParameterBinderInterface
{
    /**
     * @param ReflectionParameter $type
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
        //TODO: If id parameter is present then load model by id
        //TODO: Load model values from post request
        $paramInfo = ParameterInfo::fromParameter($param);

        if (!$paramInfo->isInstanceOf("yii\\db\\ActiveRecord")) {
            return null;
        }

        $typeName = $param->getType()->getName();
        $id = $context->request->get("id");
        $result = $typeName::findOne($id);

        if ($result !== null || $paramInfo->allowsNull()) {
            return new BindingResult($result);
        }

        return null;
    }
}
