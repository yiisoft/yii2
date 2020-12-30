<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders;

class DataFilterBinder extends ParameterTypeFactoryInterace {

    /**
     * @inheritdoc
     */
    public function canCreateType($type) {
        // TODO: Check if type is derived from DataFilter instance
    }

    /**
     * @inheritdoc
     */
    public function createType($type, $context) {
        $typeName = $type->getType()->getName();
        $dataFilter = new $typeName;
        $dataFilter->load($context->request->getBodyParams());
        return $typeName;
    }
}

