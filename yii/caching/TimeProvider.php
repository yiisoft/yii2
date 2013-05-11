<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * TimeProvider is the class for providing time for Cache.
 */

class TimeProvider {

    private static $now;

    /**
     * Return current time. Current time for testing can be set by method [[setTime]].
     *
     * @return integer Current time
     */
    public static function time () {
        return TimeProvider::$now ?: time();
    }

    /**
     * Set current time for testing purposes.
     */
    public static function setTime ($time) {
        TimeProvider::$now = $time;
    }

}