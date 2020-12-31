<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\binders\system;

use yii\binders\BindingResult;
use yii\binders\ParameterBinderInterface;

class ContainerBinder implements ParameterBinderInterface {
    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context) {
        //TODO: Use container to bind parameter
    }
}
