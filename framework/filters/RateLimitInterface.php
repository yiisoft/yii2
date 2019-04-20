<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

/**
 * RateLimitInterface 是可由标识对象实现以实施速率限制的接口。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface RateLimitInterface
{
    /**
     * 返回允许的最大请求数和窗口大小。
     * @param \yii\web\Request $request 当前请求
     * @param \yii\base\Action $action 要执行的操作
     * @return array 由两个元素组成的数组。第一个元素是允许的最大请求数，
     * 第二个元素是以秒为单位的窗口大小。
     */
    public function getRateLimit($request, $action);

    /**
     * 从持久存储加载允许的请求数和相应的时间戳。
     * @param \yii\web\Request $request 当前请求
     * @param \yii\base\Action $action 要执行的操作
     * @return array 由两个元素组成的数组。第一个元素是允许的请求数，
     * 第二个元素是相应的 UNIX 时间戳。
     */
    public function loadAllowance($request, $action);

    /**
     * 将允许的请求数和相应的时间戳保存到持久存储中。
     * @param \yii\web\Request $request 当前请求
     * @param \yii\base\Action $action 要执行的操作
     * @param int $allowance 允许的剩余请求数。
     * @param int $timestamp 当前时间戳。
     */
    public function saveAllowance($request, $action, $allowance, $timestamp);
}
