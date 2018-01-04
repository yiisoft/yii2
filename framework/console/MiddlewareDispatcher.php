<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\base\Component;
use yii\base\MiddlewareDispatcherInterface;

/**
 * MiddlewareDispatcher
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class MiddlewareDispatcher extends Component implements MiddlewareDispatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($request, array $middleware, $handler)
    {
        if (empty($middleware)) {
            return call_user_func($handler, $request);
        }
        // @todo process middleware stack
        return call_user_func($handler, $request);
    }
}