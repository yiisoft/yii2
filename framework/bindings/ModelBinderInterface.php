<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

interface ModelBinderInterface
{
    /**
     * @param BindingTargetInterface $target
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($target, $context);
}
