<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings\mocks;

class Post extends \yii\db\ActiveRecord
{
    public $findOneCalled = false;
    public $setAttributesCalled = false;
    public $arguments = [];

    public static function findOne($condition)
    {
        $instance =  new static();
        $instance->findOneCalled = true;
        $instance->arguments['findOne'] = [
            'condition' => $condition
        ];
        return $instance;
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $this->setAttributesCalled = true;
        $this->arguments['setAttributes'] = [
            'values' => $values,
            'safeOnly' => $safeOnly
        ];
        parent::setAttributes($values, $safeOnly);
    }
}
