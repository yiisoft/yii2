<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\base\BaseObject;
use yii\bindings\BindingResult;
use yii\bindings\ModelBinderInterface;

final class DataFilterBinder extends BaseObject implements ModelBinderInterface
{
    public function bindModel($target, $context)
    {
        if (!$target->isInstanceOf("yii\\data\\DataFilter")) {
            return null;
        }

        $typeName = $target->getTypeName();
        $dataFilter = \Yii::createObject($typeName);

        if ($context->request->getIsGet()) {
            $params = $target->getValue();
        } else {
            $params = $context->request->getBodyParams();
        }

        //TODO: Convert params to array

        $dataFilter->load($params);
        return new BindingResult($dataFilter);
    }
}
