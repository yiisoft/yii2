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
class Qux extends BaseObject implements QuxInterface
{
    public function __construct(public $a = 1, $config = [])
    {
        parent::__construct($config);
    }

    public function quxMethod(): void
    {
    }
}
