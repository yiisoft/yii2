<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use yii\base\BaseObject;

final class CompositeModelBinder extends BaseObject implements ModelBinderInterface
{
    private $binderInstances = null;
    public $binders = [];

    public function getModelBinders()
    {
        if ($this->binderInstances === null) {
            $binders = array_merge($this->getDefaultBinders(), $this->binders);

            $result = [];
            foreach ($binders as $key => $config) {
                $result[$key] = \Yii::createObject($config);
            }
            $this->binderInstances = $result;
        }

        return $this->binderInstances;
    }

    private function getDefaultBinders()
    {
        return [
            '@builtin' => 'yii\\bindings\\binders\\BuiltinTypeBinder',
            '@activeRecord' => 'yii\\bindings\\binders\\ActiveRecordBinder',
            '@model' => 'yii\\bindings\\binders\\ModelBinder',
            '@dataFilter' => 'yii\\bindings\\binders\\DataFilterBinder',
            '@dateTime' =>'yii\\bindings\\binders\\DateTimeBinder',
            '@container' => 'yii\\bindings\\binders\\ContainerBinder',
            '@classType' => 'yii\\bindings\\binders\\ClassTypeBinder',
        ];
    }

    public function bindModel($param, $context)
    {
        $binders = $this->getModelBinders();

        foreach ($binders as $binder) {
            $result = $binder->bindModel($param, $context);
            if ($result instanceof BindingResult) {
                return $result;
            }
        }

        return null;
    }
}
