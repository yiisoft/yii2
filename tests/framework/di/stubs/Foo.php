<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Foo extends BaseObject
{
    public $bar;

    public function __construct(Bar $bar, $config = [])
    {
        $this->bar = $bar;
        parent::__construct($config);
    }
}
