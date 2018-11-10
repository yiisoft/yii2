<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex\mocks;

use yii\mutex\Mutex;
use yii\mutex\RetryAcquireTrait;

/**
 * Class DumbMutex.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 */
class DumbMutex extends Mutex
{
    use RetryAcquireTrait;

    public $attemptsCounter = 0;
    public static $locked = false;

    /**
     * {@inheritdoc}
     */
    protected function acquireLock($name, $timeout = 0)
    {
        return $this->retryAcquire($timeout, function () {
            $this->attemptsCounter++;
            if (!static::$locked) {
                static::$locked = true;

                return true;
            }

            return false;
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function releaseLock($name)
    {
        if (static::$locked) {
            static::$locked = false;

            return true;
        }

        return false;
    }
}
