<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\di\Container;

class QuxFactory extends \yii\base\BaseObject
{
    public static function create(Container $container)
    {
        return new Qux(42);
    }
}
