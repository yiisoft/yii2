<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use yii\base\BaseObject;

class BindingRegistry extends BaseObject implements ParameterBinderInterface
{
    public function getBinders() {
        return [];
    }

    protected function getDefaultBinders() {
        return [
            'yii\binders\system\BuiltinTypeBinder',
            'yii\binders\system\ContainerTypeBinder',
            'yii\binders\system\ActiveRecordBinder',
            'yii\binders\system\DataFilterBinder',
            'yii\binders\system\DateTimeBinder',
            'yii\binders\system\ClassTypeBinder',
        ];
    }

    /**
     * @inheritdoc
     */
    public function bindModel($type, $context)
    {
        $binders = $this->getBinders();
        foreach($binders as $binder) {
            $result = $binder->bindModel($type, $context);
            if ($result instanceof BindingResult) {
                return $result;
            }
        }
        return null;
    }
}
