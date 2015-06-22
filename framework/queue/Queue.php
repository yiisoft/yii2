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
     * Push a new job to the queue.
     *
     * @param string $job
     * @param mixed|null $data
     * @param string|null $queue
     * @param integer $delay
     */
    abstract public function push($job, $data = null, $queue = null, $delay = 0);

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     *
     * @return Job
     */
    abstract public function pop($queue = null);

    /**
     * Return the size (number of pending jobs) of the specified queue.
     *
     * @param string|null $queue
     *
     * @return integer
     */
    abstract public function size($queue = null);
}
