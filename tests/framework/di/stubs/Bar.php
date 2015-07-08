<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\base\Object;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Bar extends Object
{
    public $qux;

    public function __construct(QuxInterface $qux, $config = [])
    {
        $this->qux = $qux;
        parent::__construct($config);
    }
}
