<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class DataFilterBinder extends BaseObject implements ParameterBinderInterface
{
    public function bindModel($param, $context)
    {
        if (!$param->isInstanceOf("yii\\data\\DataFilter")) {
            return null;
        }

        $typeName = $param->getTypeName();
        $dataFilter = \Yii::createObject($typeName);

        if ($context->request->getIsGet()) {
            $params = $param->value;
        } else {
            $params = $context->request->getBodyParams();
        }

        //TODO: Convert params to array

        $dataFilter->load($params);
        return new BindingResult($dataFilter);
    }
}
