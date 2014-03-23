<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

/**
 * RateLimitInterface is the interface that may be implemented by an identity object to enforce rate limiting.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface RateLimitInterface
{
    /**
     * Returns the maximum number of allowed requests and the window size.
     * @param array $params the additional parameters associated with the rate limit.
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     */
    public function getRateLimit($params = []);
    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param array $params the additional parameters associated with the rate limit.
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($params = []);
    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param integer $allowance the number of allowed requests remaining.
     * @param integer $timestamp the current timestamp.
     * @param array $params the additional parameters associated with the rate limit.
     */
    public function saveAllowance($allowance, $timestamp, $params = []);
}
