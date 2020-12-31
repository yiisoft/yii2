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
    private $_binders = null;

    public function getBinders()
    {
        return $this->_binders;
    }

    protected function getDefaultBinders()
    {
        return [
            'builtin' => 'yii\bindings\binders\BuiltinTypeBinder',
            'activeRecord' => 'yii\bindings\binders\ActiveRecordBinder',
            'dataFilter' => 'yii\bindings\binders\DataFilterBinder',
            'dateTime' =>'yii\bindings\binders\DateTimeBinder',
            'container' => 'yii\bindings\binders\ContainerTypeBinder',
            'type' => 'yii\bindings\binders\ClassTypeBinder',
        ];
    }

    /**
     * @param ReflectionParameter $param
     * @param BindingContext $context
     * @return BindingResult | null
     */
    public function bindModel($param, $context)
    {
        $binders = $this->getBinders();
        foreach($binders as $binder) {
            $result = $binder->bindModel($param, $context);
            if ($result instanceof BindingResult) {
                return $result;
            }
        }
        return null;
    }
}
