<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;
use Yii;

/**
 * @author Yusup Hambali <supalpuket@gmail.com>
 * @since 2.0.31
 */
class FooBaz extends BaseObject
{
    public $fooDependent = [];

    public function init(): void
    {
        // default config usually used by Yii
        $dependentConfig = array_merge(['class' => FooDependent::class], $this->fooDependent);
        $this->fooDependent = Yii::createObject($dependentConfig);
    }
}

class FooDependent extends BaseObject
{
}

class FooDependentSubclass extends FooDependent
{
}
