<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use yii\base\BaseObject;

class BindingRegistry extends BaseObject implements ModelBinderInterface
{
    private $_binders = null;
    private $binders = [];

    public function getBinders()
    {
        if (is_null($this->_binders)) {
            $binders = array_merge($this->binders, $this->getDefaultBinders());

            $result = [];
            foreach ($binders as $config) {
                $result[] = \Yii::createObject($config);
            }
            $this->_binders = $result;
        }

        return $this->_binders;
    }

    protected function getDefaultBinders()
    {
        return [
            'builtin' => 'yii\\bindings\\binders\\BuiltinTypeBinder',
            'activeRecord' => 'yii\\bindings\\binders\\ActiveRecordBinder',
            'dataFilter' => 'yii\\bindings\\binders\\DataFilterBinder',
            'dateTime' =>'yii\\bindings\\binders\\DateTimeBinder',
            'container' => 'yii\\bindings\\binders\\ContainerBinder',
            'classType' => 'yii\\bindings\\binders\\ClassTypeBinder',
        ];
    }

    public function bindModel($param, $context)
    {
        $binders = $this->getBinders();

        foreach ($binders as $binder) {
            $result = $binder->bindModel($param, $context);
            if ($result instanceof BindingResult) {
                return $result;
            }
        }
        return null;
    }
}
