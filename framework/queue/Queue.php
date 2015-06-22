<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use yii\base\Component;

/**
 * Queue is the base class for queue classes supporting different queue back-ends.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0.5
 */
abstract class Queue extends Component
{
    /**
     * Push a new message to the queue.
     *
     * @param string $message
     * @param string|null $queue
     * @param integer $delay
     */
    abstract public function push($message, $queue = null, $delay = 0);

    /**
     * Pop the next message off of the queue.
     *
     * @param string|null $queue
     *
     * @return string
     */
    abstract public function pop($queue = null);
}
