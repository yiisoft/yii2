<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\bindings\BindingResult;
use yii\bindings\ParameterBinderInterface;

class ActiveRecordBinder extends ParameterBinderInterface {
    /**
     * @param ReflectionParameter $type
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($type, $context) {
        $typeName = $type->getType()->getName();
        $id = $context->request->get("id");
        $result = $typeName::findOne($id);

        if ($result !== null || $type->allowsNull()) {
            return new BindingResult($result);
        }
        return null;
    }
}
