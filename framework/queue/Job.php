<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Yii;
use yii\base\Object;

/**
 * Job is the base class for queue job classes.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0.5
 */
abstract class Job extends Object
{
    /**
     * Returns the queue connection used by this Job class.
     * By default, the "queue" application component is used as the queue connection.
     * You may override this method if you want to use a different queue connection.
     * @return Queue the queue connection used by this Job class.
     */
    public static function getQueue()
    {
        return Yii::$app->getQueue();
    }

    /**
     * Push this job onto the queue.
     *
     * @param string|null $queue
     * @param integer $delay
     */
    public static function push($queue = null, $delay = 0)
    {
        // implementation
    }
}
