<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders;

class ActiveRecordBinder extends ParameterTypeFactoryInterace {

    /**
     * @inheritdoc
     */
    public function canCreateType($type) {
        // TODO: Check if type is derived from ActiveRecord instance
    }

    /**
     * @inheritdoc
     */
    public function createType($type, $context) {
        $typeName = $type->getType()->getName();
        $id = \Yii::$app->request->get("id");
        return $typeName::findOne($id);
    }
}