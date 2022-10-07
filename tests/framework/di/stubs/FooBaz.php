<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

/**
 * @author Yusup Hambali <supalpuket@gmail.com>
 * @since 2.0.31
 */
class FooBaz extends \yii\base\BaseObject
{
    public $fooDependent = [];

    public function init()
    {
        // default config usually used by Yii
        $dependentConfig = array_merge(['class' => FooDependent::className()], $this->fooDependent);
        $this->fooDependent = \Yii::createObject($dependentConfig);
    }
}

class FooDependent extends \yii\base\BaseObject
{
}

class FooDependentSubclass extends FooDependent
{
}
