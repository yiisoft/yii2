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

class ActiveRecordBinder extends BaseObject implements ParameterBinderInterface
{
    public function bindModel($param, $context)
    {
        //TODO: If id parameter is present then load model by id
        //TODO: Load model values from post request

        if (!$param->isInstanceOf("yii\\db\\ActiveRecord")) {
            return null;
        }

        $id = $context->getParameterValue("id");

        $typeName = $param->getTypeName();
        $result = $typeName::findOne($id);

        if ($result !== null || $param->allowsNull()) {
            return new BindingResult($result);
        }

        return null;
    }
}
