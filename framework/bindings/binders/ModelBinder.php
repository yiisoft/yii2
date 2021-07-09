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

final class ModelBinder extends BaseObject implements ModelBinderInterface
{
    public function bindModel($target, $context)
    {
        if (!$target->isInstanceOf("yii\\base\\Model")) {
            return null;
        }

        $typeName = $target->getTypeName();

        $result = \Yii::createObject($typeName);
        $result->load($context->request->post());

        return new BindingResult($result);
    }
}
