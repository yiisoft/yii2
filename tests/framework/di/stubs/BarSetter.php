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
 * @property QuxInterface $qux
 */
class BarSetter extends BaseObject
{
    /**
     * @var QuxInterface
     */
    private $qux;

    /**
     * @return QuxInterface
     */
    public function getQux()
    {
        return $this->qux;
    }

    /**
     * @param mixed $qux
     */
    public function setQux(QuxInterface $qux)
    {
        $this->qux = $qux;
    }
}
