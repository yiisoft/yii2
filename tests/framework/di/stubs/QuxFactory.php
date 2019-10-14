<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\di\Container;

class QuxFactory
{
    public static function create(Container $container)
    {
        return new Qux(42);
    }
}
