<?php
/**
 * @link      https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 *
 * @property BarSetter $bar
 */
class FooProperty extends BaseObject
{
    /**
     * @var BarSetter
     */
    public $bar;
}
