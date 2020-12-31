<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders\system;

use yii\binders\BindingResult;
use yii\binders\ParameterBinderInterface;

class DataFilterBinder implements ParameterBinderInterface {
    public function bindModel($type, $context) {
        $typeName = $type->getType()->getName();
        $dataFilter = new $typeName;
        $dataFilter->load($context->request->getBodyParams());
        return new BindingResult($dataFilter);
    }
}
