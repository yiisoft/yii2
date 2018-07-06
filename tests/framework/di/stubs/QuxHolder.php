<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

/**
 * Plain object. Not Configurable.
 * @author Andrii Vasyliev <sol@hiqdev.com>
 * @since 3.0.0
 */
class QuxHolder
{
    /**
     * @var QuxInterface
     */
    private $qux;

    /**
     * @var QuxInterface
     */
    public $otherQux;

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
