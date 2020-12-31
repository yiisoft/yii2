<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings\binders;

use yii\bindings\binders\BindingResult;
use yii\bindings\binders\ParameterBinderInterface;

class ActiveRecordBinder extends ParameterBinderInterface {

    /**
     * @inheritdoc
     */
    public function bindModel($type, $context) {
        $typeName = $type->getType()->getName();
        $id = \Yii::$app->request->get("id");
        return $typeName::findOne($id);
    }
}
