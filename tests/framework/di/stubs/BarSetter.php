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
    private ?\yiiunit\framework\di\stubs\QuxInterface $qux = null;

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
    public function setQux(QuxInterface $qux): void
    {
        $this->qux = $qux;
    }
}
