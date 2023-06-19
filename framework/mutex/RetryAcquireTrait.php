<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Closure;

/**
 * Trait RetryAcquireTrait.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @internal
 * @since 2.0.16
 */
trait RetryAcquireTrait
{
    /**
     * @var int Number of milliseconds between each try in [[acquire()]] until specified timeout times out.
     * By default it is 50 milliseconds - it means that [[acquire()]] may try acquire lock up to 20 times per second.
     * @since 2.0.16
     */
    public $retryDelay = 50;


    private function retryAcquire($timeout, Closure $callback)
    {
        $start = microtime(true);
        do {
            if ($callback()) {
                return true;
            }
            usleep($this->retryDelay * 1000);
        } while (microtime(true) - $start < $timeout);

        return false;
    }
}
