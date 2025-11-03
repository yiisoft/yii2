<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\filters;

use yii\base\Action;
use yii\base\Controller;
use yii\base\Module;

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
     * @param \yii\web\Request $request the current request
     * @param Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     *
     * @phpstan-param Action<Controller<Module>> $action
     * @psalm-param Action<Controller<Module>> $action
     */
    public function getRateLimit($request, $action);

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     *
     * @phpstan-param Action<Controller<Module>> $action
     * @psalm-param Action<Controller<Module>> $action
     */
    public function loadAllowance($request, $action);

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param Action $action the action to be executed
     * @param int $allowance the number of allowed requests remaining.
     * @param int $timestamp the current timestamp.
     *
     * @phpstan-param Action<Controller<Module>> $action
     * @psalm-param Action<Controller<Module>> $action
     */
    public function saveAllowance($request, $action, $allowance, $timestamp);
}
